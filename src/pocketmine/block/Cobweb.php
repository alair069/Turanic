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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\item\Tool;

class Cobweb extends Flowable {

	protected $id = self::COBWEB;

	/**
	 * Cobweb constructor.
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return bool
	 */
	public function hasEntityCollision(){
		return true;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Cobweb";
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 4;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_SHEARS;
	}

	/**
	 * @param Entity $entity
	 */
	public function onEntityCollide(Entity $entity){
		$entity->resetFallDistance();
	}

	/**
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
		if($item->isShears()){
			return [
				[Item::COBWEB, 0, 1],
			];
		}elseif($item->isSword() >= TieredTool::TIER_WOODEN){
			if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
				return [
					[Item::COBWEB, 0, 1],
				];
			}else{
				return [
					[Item::STRING, 0, 1],
				];
			}
		}
		return [];
	}

    /**
     * @return bool
     */
    public function canHarvestWithHand(): bool{
        return false;
    }
}
