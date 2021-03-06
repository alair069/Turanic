<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\entity\hostile\Husk;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Timings;
use pocketmine\item\Consumable;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\utils\BlockIterator;

abstract class Living extends Entity implements Damageable {

	protected $gravity = 0.08;
	protected $drag = 0.02;

	protected $attackTime = 0;

    /** @var int */
	public $deadTicks = 0;
    /** @var int */
    protected $maxDeadTicks = 20;

	protected $invisible = false;
	protected $jumpVelocity = 0.42;

	/** @var int */
	public $maxAir = 400;

	protected function initEntity(){
		parent::initEntity();

        $health = $this->getMaxHealth();

        if($this->namedtag->hasTag("HealF", ShortTag::class)){
            $health = $this->namedtag->getShort("HealF");
            $this->namedtag->removeTag("HealF");
        }elseif($this->namedtag->hasTag("Health")){
            $healthTag = $this->namedtag->getTag("Health");
            $health = $healthTag->getValue();
            if(!($healthTag instanceof ShortTag)){
                $this->namedtag->removeTag("Health");
            }
        }

		$this->setHealth($health);
	}

	/**
	 * @param float $amount
	 */
	public function setHealth(float $amount){
		$wasAlive = $this->isAlive();
		parent::setHealth($amount);
		if($this->isAlive() and !$wasAlive){
			$pk = new EntityEventPacket();
			$pk->entityRuntimeId = $this->getId();
			$pk->event = EntityEventPacket::RESPAWN;
			$this->server->broadcastPacket($this->hasSpawned, $pk);
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->setShort("Health", $this->getHealth());
	}

	public function jump(){
		if($this->onGround){
			$this->motionY = $this->getJumpVelocity(); //Y motion should already be 0 if we're jumping from the ground.
		}
	}

	/**
	 * Returns the initial upwards velocity of a jumping entity in blocks/tick, including additional velocity due to effects.
	 * @return float
	 */
	public function getJumpVelocity() : float{
		return $this->jumpVelocity + ($this->hasEffect(Effect::JUMP) ? ($this->getEffect(Effect::JUMP)->getAmplifier() / 10) : 0);
	}

	/**
	 * @return mixed
	 */
	public abstract function getName();

	/**
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function hasLineOfSight(Entity $entity){
		//TODO: head height
		return true;
		//return $this->getLevel()->rayTraceBlocks(Vector3::createVector($this->x, $this->y + $this->height, $this->z), Vector3::createVector($entity->x, $entity->y + $entity->height, $entity->z)) === null;
	}

	/**
	 * @param EntityRegainHealthEvent $source
	 * @internal param float $amount
	 */
	public function heal(EntityRegainHealthEvent $source){
		parent::heal($source);
		if($source->isCancelled()){
			return;
		}

		$this->attackTime = 0;
	}

	/**
	 * @param EntityDamageEvent $source
	 * @return bool|void
	 */
	public function attack(EntityDamageEvent $source){
		if($this->attackTime > 0 or $this->noDamageTicks > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause !== null and $lastCause->getDamage() >= $source->getDamage()){
				$source->setCancelled();
			}
		}

		parent::attack($source);

		if($source->isCancelled()){
			return;
		}

		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			if($source instanceof EntityDamageByChildEntityEvent){
				$e = $source->getChild();
			}

			if($e->isOnFire() and !($e instanceof Player)){
				$this->setOnFire(2 * $this->server->getDifficulty());
			}

			$deltaX = $this->x - $e->x;
			$deltaZ = $this->z - $e->z;
			$this->knockBack($e, $source->getDamage(), $deltaX, $deltaZ, $source->getKnockBack());
			if($e instanceof Husk){
				$this->addEffect(Effect::getEffect(Effect::HUNGER)->setDuration(7 * 20 * $this->server->getDifficulty()));
			}
		}

		$pk = new EntityEventPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->event = $this->getHealth() <= 0 ? EntityEventPacket::DEATH_ANIMATION : EntityEventPacket::HURT_ANIMATION; //Ouch!
		$this->server->broadcastPacket($this->hasSpawned, $pk);

		$this->attackTime = 10; //0.5 seconds cooldown
	}

	/**
	 * @param Entity $attacker
	 * @param		$damage
	 * @param		$x
	 * @param		$z
	 * @param float  $base
	 */
	public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4){
		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}

		$f = 1 / $f;

		$motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);

		$motion->x /= 2;
		$motion->y /= 2;
		$motion->z /= 2;
		$motion->x += $x * $f * $base;
		$motion->y += $base;
		$motion->z += $z * $f * $base;

		if($motion->y > $base){
			$motion->y = $base;
		}

		$this->setMotion($motion);
	}

	public function kill(){
		if(!$this->isAlive()){
			return;
		}
		parent::kill();
		$this->server->getPluginManager()->callEvent($ev = new EntityDeathEvent($this, $this->getDrops()));
		foreach($ev->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}

	protected function onDeathUpdate(int $tickDiff): bool{
        if($this->deadTicks < $this->maxDeadTicks){
            $this->deadTicks += $tickDiff;
            if($this->deadTicks >= $this->maxDeadTicks){
                //TODO: spawn experience orbs here
            }
        }

        return $this->deadTicks >= 20;
    }

    /**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1){
		Timings::$timerLivingEntityBaseTick->startTiming();
		$this->setGenericFlag(self::DATA_FLAG_BREATHING, !$this->isInsideOfWater());

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){
			if($this->isInsideOfSolid()){
				$hasUpdate = true;
				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);
				$this->attack($ev);
			}
			$maxAir = $this->maxAir;
			$this->setDataProperty(self::DATA_MAX_AIR, self::DATA_TYPE_SHORT, $maxAir);
			if(!$this->hasEffect(Effect::WATER_BREATHING) and $this->isInsideOfWater()){
				if($this instanceof WaterAnimal){
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 400);
				}else{
					$hasUpdate = true;
					$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
					if($airTicks <= -80){
						$airTicks = 0;

						$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
						$this->attack($ev);
					}
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, min($airTicks, $maxAir));
				}
			}else{
				if($this instanceof WaterAnimal){
					$hasUpdate = true;
					$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
					if($airTicks <= -80){
						$airTicks = 0;

						$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 2);
						$this->attack($ev);
					}
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $airTicks);
				}else{
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $maxAir);
				}
			}
		}

		if($this->attackTime > 0){
			$this->attackTime -= $tickDiff;
		}

		Timings::$timerLivingEntityBaseTick->stopTiming();

		return $hasUpdate;
	}

	/**
	 * @return ItemItem[]
	 */
	public function getDrops(){
		return [];
	}
	
	/**
	 * Returns the number of ticks remaining in the entity's air supply. Note that the entity may survive longer than
	 * this amount of time without damage due to enchantments such as Respiration.
	 *
	 * @return int
	 */
	public function getAirSupplyTicks() : int{
		return $this->getDataProperty(self::DATA_AIR);
	}

	/**
	 * Sets the number of air ticks left in the entity's air supply.
	 * @param int $ticks
	 */
	public function setAirSupplyTicks(int $ticks){
		$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $ticks);
	}

	/**
	 * Returns the maximum amount of air ticks the entity's air supply can contain.
	 * @return int
	 */
	public function getMaxAirSupplyTicks() : int{
		return $this->getDataProperty(self::DATA_MAX_AIR);
	}

	/**
	 * Sets the maximum amount of air ticks the air supply can hold.
	 * @param int $ticks
	 */
	public function setMaxAirSupplyTicks(int $ticks){
		$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $ticks);
	}

	/**
	 * @param int   $maxDistance
	 * @param int   $maxLength
	 * @param array $transparent
	 *
	 * @return Block[]
	 */
	public function getLineOfSight($maxDistance, $maxLength = 0, array $transparent = []){
		if($maxDistance > 120){
			$maxDistance = 120;
		}

		if(count($transparent) === 0){
			$transparent = null;
		}

		$blocks = [];
		$nextIndex = 0;

		$itr = new BlockIterator($this->level, $this->getPosition(), $this->getDirectionVector(), $this->getEyeHeight(), $maxDistance);

		while($itr->valid()){
			$itr->next();
			$block = $itr->current();
			$blocks[$nextIndex++] = $block;

			if($maxLength !== 0 and count($blocks) > $maxLength){
				array_shift($blocks);
				--$nextIndex;
			}

			$id = $block->getId();

			if($transparent === null){
				if($id !== 0){
					break;
				}
			}else{
				if(!isset($transparent[$id])){
					break;
				}
			}
		}

		return $blocks;
	}

	/**
	 * @param int   $maxDistance
	 * @param array $transparent
	 *
	 * @return Block
	 */
	public function getTargetBlock($maxDistance, array $transparent = []){
		try{
			$block = $this->getLineOfSight($maxDistance, 1, $transparent)[0];
			if($block instanceof Block){
				return $block;
			}
		}catch(\ArrayOutOfBoundsException $e){

		}

		return null;
	}

    public function doesTriggerPressurePlate() : bool{
        return true;
    }

    public function addEffect(Effect $effect):bool{
        $oldEffect = null;
        $cancelled = false;
        if(isset($this->effects[$effect->getId()])){
            $oldEffect = $this->effects[$effect->getId()];
            if(
                abs($effect->getAmplifier()) < $oldEffect->getAmplifier()
                or (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier()) and $effect->getDuration() < $oldEffect->getDuration())
            ){
                $cancelled = true;
            }
        }
        $ev = new EntityEffectAddEvent($this, $effect);
        $ev->setCancelled($cancelled);
        $this->server->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            return false;
        }
        $effect->add($this, $oldEffect);
        $this->effects[$effect->getId()] = $effect;
        $this->recalculateEffectColor();
        return true;
    }

    public function removeEffect($effectId){
        if(isset($this->effects[$effectId])){
            $effect = $this->effects[$effectId];
            $this->server->getPluginManager()->callEvent($ev = new EntityEffectRemoveEvent($this, $effect));
            if($ev->isCancelled()){
                return;
            }
            unset($this->effects[$effectId]);
            $effect->remove($this);
            $this->recalculateEffectColor();
        }
    }

    /**
     * Causes the mob to consume the given Consumable object, applying applicable effects, health bonuses, food bonuses,
     * etc.
     *
     * @param Consumable $consumable
     *
     * @return bool
     */
	public function consumeObject(Consumable $consumable) : bool{
        foreach ($consumable->getAdditionalEffects() as $effect) {
            $this->addEffect($effect);
        }

        $consumable->onConsume($this);

        return true;
    }
}