<?php

/*
 *
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
 *
*/

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\sound\TNTPrimeSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class TNT extends Solid implements ElectricalAppliance {

	protected $id = self::TNT;

    /**
     * TNT constructor.
     * @param int $meta
     */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "TNT";
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 0;
	}

    /**
	 * @return bool
	 */
	public function canBeActivated() : bool{
		return true;
	}

	/**
	 * @return int
	 */
	public function getBurnChance() : int{
		return 15;
	}

	/**
	 * @return int
	 */
	public function getBurnAbility() : int{
		return 100;
	}

    /**
     * @param Player|null $player
     * @param int $fuse
     */
	public function prime(Player $player = null, int $fuse = 80){
        $this->meta = 1;
        $dropItem = $player != null and $player->isCreative() ? false : true;
        $mot = (new Random())->nextSignedFloat() * M_PI * 2;
        $nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5), new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
        $nbt->setByte("Fuse", $fuse);

        $tnt = Entity::createEntity("PrimedTNT", $this->getLevel(), $nbt, $dropItem);

        if ($tnt !== null)
            $tnt->spawnToAll();

        $this->level->addSound(new TNTPrimeSound($this));
    }

	/**
	 * @param int $type
	 */
	public function onUpdate($type){
		if(($type == Level::BLOCK_UPDATE_NORMAL || $type == Level::BLOCK_UPDATE_REDSTONE) && $this->level->isBlockPowered($this)){
            $this->prime();
            $this->getLevel()->setBlock($this, new Air(), true);
		}
	}

	/**
	 * @param Item        $item
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::FLINT_STEEL){
			$this->prime($player);
			$this->getLevel()->setBlock($this, new Air(), true);

			$item->useOn($this);

			return true;
		}

		return false;
	}
}
