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

/**
 * All Block classes are in here
 */
namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Block extends Position implements BlockIds, Metadatable{	

	/** @var \SplFixedArray */
	public static $list = null;
	/** @var \SplFixedArray */
	public static $fullList = null;

	/** @var \SplFixedArray */
	public static $light = null;
	/** @var \SplFixedArray */
	public static $lightFilter = null;
	/** @var \SplFixedArray */
	public static $solid = null;
	/** @var \SplFixedArray */
	public static $hardness = null;
	/** @var \SplFixedArray */
	public static $transparent = null;
    /** @var \SplFixedArray */
    public static $diffusesSkyLight = null;

	protected $id;
	protected $meta = 0;

	/** @var AxisAlignedBB */
	public $boundingBox = null;

	/** @var string */
    protected $blockName;
    /** @var int|null */
    protected $itemId = null;

    /** @var AxisAlignedBB[]|null */
    protected $collisionBoxes;

    public static function init(bool $force = false){
		if(self::$list === null){
			self::$list = new \SplFixedArray(256);
			self::$fullList = new \SplFixedArray(4096);

			self::$light = new \SplFixedArray(256);
			self::$lightFilter = new \SplFixedArray(256);
			self::$solid = new \SplFixedArray(256);
			self::$hardness = new \SplFixedArray(256);
			self::$transparent = new \SplFixedArray(256);
            self::$diffusesSkyLight = new \SplFixedArray(256);

            self::registerBlock(new Air());
            self::registerBlock(new Stone());
            self::registerBlock(new Grass());
            self::registerBlock(new Dirt());
            self::registerBlock(new Cobblestone());
            self::registerBlock(new Planks());
            self::registerBlock(new Sapling());
            self::registerBlock(new Bedrock());
            self::registerBlock(new Water());
            self::registerBlock(new StillWater());
            self::registerBlock(new Lava());
            self::registerBlock(new StillLava());
            self::registerBlock(new Sand());
            self::registerBlock(new Gravel());
            self::registerBlock(new GoldOre());
            self::registerBlock(new IronOre());
            self::registerBlock(new CoalOre());
            self::registerBlock(new Wood());
            self::registerBlock(new Leaves());
            self::registerBlock(new Sponge());
            self::registerBlock(new Glass());
            self::registerBlock(new LapisOre());
            self::registerBlock(new Lapis());
            self::registerBlock(new Sandstone());
            self::registerBlock(new Dispenser());
            self::registerBlock(new Noteblock());
            self::registerBlock(new Bed());
            self::registerBlock(new PoweredRail());
            self::registerBlock(new DetectorRail());
            // TODO : Add Sticky Piston
            self::registerBlock(new Cobweb());
            self::registerBlock(new TallGrass());
            self::registerBlock(new DeadBush());
            // TODO : Add Piston
            self::registerBlock(new Wool());
            self::registerBlock(new Dandelion());
            self::registerBlock(new Flower());
            self::registerBlock(new BrownMushroom());
            self::registerBlock(new RedMushroom());
            self::registerBlock(new Gold());
            self::registerBlock(new Iron());
            self::registerBlock(new DoubleSlab());
            self::registerBlock(new Slab());
            self::registerBlock(new Bricks());
            self::registerBlock(new TNT());
            self::registerBlock(new Bookshelf());
            self::registerBlock(new MossStone());
            self::registerBlock(new Obsidian());
            self::registerBlock(new Torch());
            self::registerBlock(new Fire());
            self::registerBlock(new MonsterSpawner());
            self::registerBlock(new WoodenStairs(Block::OAK_WOOD_STAIRS, 0, "Oak Stairs"));
            self::registerBlock(new Chest());
            self::registerBlock(new RedstoneWire());
            self::registerBlock(new DiamondOre());
            self::registerBlock(new Diamond());
            self::registerBlock(new Workbench());
            self::registerBlock(new Wheat());
            self::registerBlock(new Farmland());
            self::registerBlock(new Furnace());
            self::registerBlock(new BurningFurnace());
            self::registerBlock(new SignPost());
            self::registerBlock(new WoodenDoor(Block::DOOR_BLOCK, 0, "Oak Door Block", Item::OAK_DOOR));
            self::registerBlock(new Ladder());
            self::registerBlock(new Rail());
            self::registerBlock(new CobblestoneStairs());
            self::registerBlock(new WallSign());
            self::registerBlock(new Lever());
            self::registerBlock(new StonePressurePlate());
            self::registerBlock(new IronDoor());
            self::registerBlock(new WoodenPressurePlate());
            self::registerBlock(new RedstoneOre());
            self::registerBlock(new GlowingRedstoneOre());
            // TODO: Add unlit redstone torch
            self::registerBlock(new RedstoneTorch());
            self::registerBlock(new StoneButton());
            self::registerBlock(new SnowLayer());
            self::registerBlock(new Ice());
            self::registerBlock(new Snow());
            self::registerBlock(new Cactus());
            self::registerBlock(new Clay());
            self::registerBlock(new Sugarcane());
            self::registerBlock(new Jukebox());
            self::registerBlock(new Fence());
            self::registerBlock(new Pumpkin());
            self::registerBlock(new Netherrack());
            self::registerBlock(new SoulSand());
            self::registerBlock(new Glowstone());
            self::registerBlock(new Portal());
            self::registerBlock(new LitPumpkin());
            self::registerBlock(new Cake());
            self::registerBlock(new UnpoweredRepeater());
            self::registerBlock(new PoweredRepeater());
            self::registerBlock(new InvisibleBedrock());
            self::registerBlock(new Trapdoor());
            self::registerBlock(new MonsterEggBlock());
            self::registerBlock(new StoneBricks());
            self::registerBlock(new BrownMushroomBlock());
            self::registerBlock(new RedMushroomBlock());
            self::registerBlock(new IronBars());
            self::registerBlock(new GlassPane());
            self::registerBlock(new Melon());
            self::registerBlock(new PumpkinStem());
            self::registerBlock(new MelonStem());
            self::registerBlock(new Vine());
            self::registerBlock(new FenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"));
            self::registerBlock(new BrickStairs());
            self::registerBlock(new StoneBrickStairs());
            self::registerBlock(new Mycelium());
            self::registerBlock(new WaterLily());
            self::registerBlock(new NetherBrick());
            self::registerBlock(new NetherBrickFence());
            self::registerBlock(new NetherBrickStairs());
            self::registerBlock(new NetherWartPlant());
            self::registerBlock(new EnchantingTable());
            self::registerBlock(new BrewingStand());
            self::registerBlock(new Cauldron());
            self::registerBlock(new EndPortal());
            self::registerBlock(new EndPortalFrame());
            self::registerBlock(new EndStone());
            self::registerBlock(new DragonEgg());
            self::registerBlock(new RedstoneLamp());
            self::registerBlock(new LitRedstoneLamp());
            self::registerBlock(new Dropper());
            self::registerBlock(new ActivatorRail());
            self::registerBlock(new CocoaBlock());
            self::registerBlock(new SandstoneStairs());
            self::registerBlock(new EmeraldOre());
            self::registerBlock(new EnderChest());
            self::registerBlock(new TripwireHook());
            self::registerBlock(new Tripwire());
            self::registerBlock(new Emerald());
            self::registerBlock(new WoodenStairs(Block::SPRUCE_STAIRS, 0, "Spruce Stairs"));
            self::registerBlock(new WoodenStairs(Block::BIRCH_STAIRS, 0, "Birch Stairs"));
            self::registerBlock(new WoodenStairs(Block::JUNGLE_STAIRS, 0, "Jungle Stairs"));
            self::registerBlock(new CommandBlock());
            self::registerBlock(new Beacon());
            self::registerBlock(new StoneWall());
            self::registerBlock(new FlowerPot());
            self::registerBlock(new Carrot());
            self::registerBlock(new Potato());
            self::registerBlock(new WoodenButton());
            self::registerBlock(new Skull());
            self::registerBlock(new Anvil());
            self::registerBlock(new TrappedChest());
            self::registerBlock(new LightWeightedPressurePlate());
            self::registerBlock(new HeavyWeightedPressurePlate());
            self::registerBlock(new UnpoweredComparator());
            self::registerBlock(new PoweredComparator());
            self::registerBlock(new DaylightDetector());
            self::registerBlock(new Redstone());
            self::registerBlock(new NetherQuartzOre());
            self::registerBlock(new Hopper());
            self::registerBlock(new Quartz());
            self::registerBlock(new QuartzStairs());
            self::registerBlock(new DoubleWoodSlab());
            self::registerBlock(new WoodSlab());
            self::registerBlock(new StainedClay());
            self::registerBlock(new StainedGlassPane());
            self::registerBlock(new Leaves2());
            self::registerBlock(new Wood2());
            self::registerBlock(new WoodenStairs(Block::ACACIA_STAIRS, 0, "Acacia Stairs"));
            self::registerBlock(new WoodenStairs(Block::DARK_OAK_STAIRS, 0, "Dark Oak Stairs"));
            self::registerBlock(new SlimeBlock());
            self::registerBlock(new IronTrapdoor());
            self::registerBlock(new Prismarine());
            self::registerBlock(new SeaLantern());
            self::registerBlock(new HayBale());
            self::registerBlock(new Carpet());
            self::registerBlock(new HardenedClay());
            self::registerBlock(new Coal());
            self::registerBlock(new PackedIce());
            self::registerBlock(new DoublePlant());
            self::registerBlock(new StandingBanner());
            self::registerBlock(new WallBanner());
            self::registerBlock(new DaylightDetectorInverted());
            self::registerBlock(new RedSandstone());
            self::registerBlock(new RedSandstoneStairs());
            self::registerBlock(new DoubleRedSandstoneSlab());
            self::registerBlock(new RedSandstoneSlab());
            self::registerBlock(new FenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"));
            self::registerBlock(new FenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"));
            self::registerBlock(new FenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"));
            self::registerBlock(new FenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"));
            self::registerBlock(new FenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"));
            self::registerBlock(new RepeatingCommandBlock());
            self::registerBlock(new ChainCommandBlock());
            self::registerBlock(new WoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door Block", Item::SPRUCE_DOOR));
            self::registerBlock(new WoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door Block", Item::BIRCH_DOOR));
            self::registerBlock(new WoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door Block", Item::JUNGLE_DOOR));
            self::registerBlock(new WoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door Block", Item::ACACIA_DOOR));
            self::registerBlock(new WoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door Block", Item::DARK_OAK_DOOR));
            self::registerBlock(new GrassPath());
            self::registerBlock(new ItemFrame());
            self::registerBlock(new ChorusFlower());
            self::registerBlock(new Purpur());
            self::registerBlock(new PurpurStairs());
            self::registerBlock(new UndyedShulkerBox());
            self::registerBlock(new EndStoneBricks());
            self::registerBlock(new FrostedIce());
            self::registerBlock(new EndRod());
            self::registerBlock(new EndGateway());
            self::registerBlock(new Magma());
            self::registerBlock(new NetherWartBlock());
            self::registerBlock(new RedNetherBrick());
            self::registerBlock(new BoneBlock());
            self::registerBlock(new ShulkerBox());
            self::registerBlock(new GlazedTerracotta(Block::PURPLE_GLAZED_TERRACOTTA, 0, "Purple Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::WHITE_GLAZED_TERRACOTTA, 0, "White Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::ORANGE_GLAZED_TERRACOTTA, 0, "Orange Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::MAGENTA_GLAZED_TERRACOTTA, 0, "Magenta Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::LIGHT_BLUE_GLAZED_TERRACOTTA, 0, "Light Blue Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::YELLOW_GLAZED_TERRACOTTA, 0, "Yellow Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::LIME_GLAZED_TERRACOTTA, 0, "Lime Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::PINK_GLAZED_TERRACOTTA, 0, "Pink Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::GRAY_GLAZED_TERRACOTTA, 0, "Grey Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::SILVER_GLAZED_TERRACOTTA, 0, "Light Grey Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::CYAN_GLAZED_TERRACOTTA, 0, "Cyan Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::BLUE_GLAZED_TERRACOTTA, 0, "Blue Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::BROWN_GLAZED_TERRACOTTA, 0, "Brown Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::GREEN_GLAZED_TERRACOTTA, 0, "Green Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::RED_GLAZED_TERRACOTTA, 0, "Red Glazed Terracotta"));
            self::registerBlock(new GlazedTerracotta(Block::BLACK_GLAZED_TERRACOTTA, 0, "Black Glazed Terracotta"));
            self::registerBlock(new Concrete());
            self::registerBlock(new ConcretePowder());
            self::registerBlock(new ChorusPlant());
            self::registerBlock(new StainedGlass());
            self::registerBlock(new Podzol());
            self::registerBlock(new Beetroot());
            self::registerBlock(new Stonecutter());
            self::registerBlock(new GlowingObsidian());
            self::registerBlock(new NetherReactor());
            /* TODO : ADD
            UPDATE_BLOCK
            ATEUPD_BLOCK
            BLOCK_MOVED_BY_PISTON
            OBSERVER
            STRUCTURE_BLOCK
            RESERVED6 */
            foreach(self::$list as $id => $block){
                if($block === null){
                    self::registerBlock(new UnknownBlock($id));
                }
            }
		}
	}

    /**
     * Registers a block type into the index. Plugins may use this method to register new block types or override
     * existing ones.
     *
     * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
     * will not automatically appear there.
     *
     * @param Block $block
     * @param bool  $override Whether to override existing registrations
     *
     * @throws \RuntimeException if something attempted to override an already-registered block without specifying the
     * $override parameter.
     */
    public static function registerBlock(Block $block, bool $override = false){
        $id = $block->getId();

        if(!$override and self::isRegistered($id)){
            throw new \RuntimeException("Trying to overwrite an already registered block (id: $id)");
        }

        self::$list[$id] = clone $block;

        for($meta = 0; $meta < 16; ++$meta){
            $variant = clone $block;
            $variant->setDamage($meta);
            self::$fullList[($id << 4) | $meta] = $variant;
        }

        self::$solid[$id] = $block->isSolid();
        self::$transparent[$id] = $block->isTransparent();
        self::$hardness[$id] = $block->getHardness();
        self::$light[$id] = $block->getLightLevel();
        self::$lightFilter[$id] = $block->getLightFilter() + 1; //opacity plus 1 standard light filter
        self::$diffusesSkyLight[$id] = $block->diffusesSkyLight();
    }

    /**
     * Returns whether a specified block ID is already registered in the block.
     *
     * @param int $id
     * @return bool
     */
    public static function isRegistered(int $id) : bool{
        $b = self::$list[$id];
        return $b !== null and !($b instanceof UnknownBlock);
    }

    /**
     * @return bool
     */
    public function canHarvestWithHand() : bool{
	    return true;
    }

    public function canBeClimbed() : bool{
        return false;
    }

    /**
     * Returns a new Block instance with the specified ID, meta and position.
     *
     * @param int      $id
     * @param int      $meta
     * @param Position $pos
     *
     * @return Block
     */
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
        if($meta < 0 or $meta > 0xf){
            throw new \InvalidArgumentException("Block meta value $meta is out of bounds");
        }

        try{
            if(self::$fullList !== null){
                $block = clone self::$fullList[($id << 4) | $meta];
            }else{
                $block = new UnknownBlock($id, $meta);
            }
        }catch(\RuntimeException $e){
            throw new \InvalidArgumentException("Block ID $id is out of bounds");
        }

        if($pos !== null){
            $block->x = $pos->x;
            $block->y = $pos->y;
            $block->z = $pos->z;
            $block->level = $pos->level;
        }

        return $block;
	}

    /**
     * @param int $id
     * @param int $meta
     * @param string $name
     * @param int|null $itemId
     */
	public function __construct(int $id, int $meta = 0, string $name = "Unknown", int $itemId = null){
		$this->id = $id;
		$this->meta = $meta;
        $this->blockName = $name;
        $this->itemId = $itemId;
	}

	/**
	 * Places the Block, using block space and block target, and side. Returns if the block has been placed.
	 *
	 * @param Item   $item
	 * @param Block  $block
	 * @param Block  $target
	 * @param int    $face
	 * @param float  $fx
	 * @param float  $fy
	 * @param float  $fz
	 * @param Player $player = null
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	public function clearCaches(){
	    $this->boundingBox = null;
	    $this->collisionBoxes = null;
    }

	/**
	 * Returns if the item can be broken with an specific Item
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function isBreakable(Item $item){
		return true;
	}

    /**
     * @return int
     */
	public function getToolType() {
        return Tool::TYPE_NONE;
 	}

	public function getToolHarvestLevel() : int{
        return 0;
 	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item $item
	 *
	 * @return mixed
	 */
	public function onBreak(Item $item){
		return $this->getLevel()->setBlock($this, new Air(), true, true);
	}

	/**
	 * Fires a block update on the Block
	 *
	 * @param int $type
	 *
	 * @return void
	 */
	public function onUpdate($type){

	}

	/**
	 * Do actions when activated by Item. Returns if it has done anything
	 *
	 * @param Item   $item
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function onActivate(Item $item, Player $player = null){
		return false;
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 10;
	}

	/**
	 * @return int
	 */
	public function getResistance(){
		return $this->getHardness() * 5;
	}

	/**
	 * @return int
	 */
	public function getBurnChance() : int{
		return 0;
	}

	/**
	 * @return int
	 */
	public function getBurnAbility() : int{
		return 0;
	}

	public function isTopFacingSurfaceSolid(){
		if($this->isSolid()){
			return true;
		}else{
			if($this instanceof Stair and ($this->getDamage() &4) == 4){
				return true;
			}elseif($this instanceof Slab and ($this->getDamage() & 8) == 8){
				return true;
			}elseif($this instanceof SnowLayer and ($this->getDamage() & 7) == 7){
				return true;
			}
		}
		return false;
	}

	public function canNeighborBurn(){
		for($face = 0; $face < 5; $face++){
			if($this->getSide($face)->getBurnChance() > 0){
				return true;
			}
		}
		return false;
	}

	/**
	 * @return float
	 */
	public function getFrictionFactor(){
		return 0.6;
	}

	/**
	 * @return int 0-15
	 */
	public function getLightLevel(){
		return 0;
	}

    /**
     * Returns the amount of light this block will filter out when light passes through this block.
     * This value is used in light spread calculation.
     *
     * @return int 0-15
     */
    public function getLightFilter() : int{
        return 15;
    }

    /**
     * Returns whether this block will diffuse sky light passing through it vertically.
     * Diffusion means that full-strength sky light passing through this block will not be reduced, but will start being filtered below the block.
     * Examples of this behaviour include leaves and cobwebs.
     *
     * Light-diffusing blocks are included by the heightmap.
     *
     * @return bool
     */
    public function diffusesSkyLight() : bool{
        return false;
    }

	/**
	 * AKA: Block->isPlaceable
	 *
	 * @return bool
	 */
	public function canBePlaced(){
		return true;
	}

	public function isPlaceable(){
		return $this->canBePlaced();
	}

	/**
	 * AKA: Block->canBeReplaced()
	 *
	 * @return bool
	 */
	public function canBeReplaced(){
		return false;
	}

    public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
        return $blockReplace->canBeReplaced();
    }

	/**
	 * @return bool
	 */
	public function isTransparent(){
		return false;
	}

	public function isSolid(){
		return true;
	}

	public function isNormal(){
	    return !$this->isTransparent() && $this->isSolid() && !$this->isRedstoneSource();
    }

    public function isRedstoneSource(){
	    return false;
    }

    public function getWeakPower(int $side) : int{
        return 0;
    }

	/**
	 * AKA: Block->isFlowable
	 *
	 * @return bool
	 */
	public function canBeFlowedInto(){
		return false;
	}

	/**
	 * AKA: Block->isActivable
	 *
	 * @return bool
	 */
	public function canBeActivated() : bool{
		return false;
	}

	public function activate(){ // TODO : remove
		return false;
	}

	public function deactivate(){
		return false;
	}

	public function isActivated(Block $from = null){
		return false;
	}

	public function hasEntityCollision(){
		return false;
	}

	public function canPassThrough(){
		return false;
	}

	/**
	 * @return string
	 */
	public function getName(){
		return $this->blockName;
	}

    /**
     * Returns the ID of the item form of the block.
     * Used for drops for blocks (some blocks such as doors have a different item ID).
     *
     * @return int
     */
    public function getItemId() : int{
        return $this->itemId ?? $this->getId();
    }

	/**
	 * @return int
	 */
	final public function getId(){
		return $this->id;
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector){

	}

	/**
	 * @return int
	 */
	final public function getDamage(){
		return $this->meta;
	}

	/**
	 * @param int $meta
	 */
	final public function setDamage($meta){
        if($meta < 0 or $meta > 0xf){
            throw new \InvalidArgumentException("Block damage values must be 0-15, not $meta");
        }
        $this->meta = $meta;
	}

	/**
	 * Sets the block position to a new Position object
	 *
	 * @param Position $v
	 */
	final public function position(Position $v){
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->level = $v->level;
		$this->boundingBox = null;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
        return [
            [$this->getItemId(), $this->getVariant(), 1],
        ];
	}

	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @param Item $item
	 *
	 * @return float
	 */
	public function getBreakTime(Item $item){
        $base = $this->getHardness();
        if($this->canBeBrokenWith($item)){
            $base *= 1.5;
        }else{
            $base *= 5;
        }

        $efficiency = $item->getMiningEfficiency($this);
        if($efficiency <= 0){
            throw new \RuntimeException("Item efficiency is invalid");
        }

        $base /= $efficiency;

        return $base;
	}

	public function canBeBrokenWith(Item $item){
        if($this->getHardness() < 0){
            return false;
        }

        $toolType = $this->getToolType();
        $harvestLevel = $this->getToolHarvestLevel();
        return $toolType === Tool::TYPE_NONE or $harvestLevel === 0 or (
                ($toolType & $item->getBlockToolType()) !== 0 and $item->getBlockToolHarvestLevel() >= $harvestLevel);
	}

	/**
	 * Returns the Block on the side $side, works like Vector3::side()
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Block
	 */
	public function getSide($side, $step = 1){
		if($this->isValid()){
			return $this->getLevel()->getBlock(Vector3::getSide($side, $step));
		}

		return Block::get(Item::AIR, 0, Position::fromObject(Vector3::getSide($side, $step)));
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getDamage() . ")";
	}

	/**
	 * Checks for collision against an AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 *
	 * @return bool
	 */
	public function collidesWithBB(AxisAlignedBB $bb){
		$bb2 = $this->getBoundingBox();

		return $bb2 !== null and $bb->intersectsWith($bb2);
	}

	/**
	 * @param Entity $entity
	 */
	public function onEntityCollide(Entity $entity){

	}

    /**
     * @return AxisAlignedBB[]
     */
    public function getCollisionBoxes() : array{
        if($this->collisionBoxes === null){
            $this->collisionBoxes = $this->recalculateCollisionBoxes();
        }

        return $this->collisionBoxes;
    }

    /**
     * @return AxisAlignedBB[]
     */
    protected function recalculateCollisionBoxes() : array{
        if($bb = $this->recalculateBoundingBox()){
            return [$bb];
        }

        return [];
    }

	/**
	 * @return AxisAlignedBB
	 */
	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	/**
	 * @return AxisAlignedBB
	 */
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

    /**
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @return null|MovingObjectPosition
     */
    public function calculateIntercept(Vector3 $pos1, Vector3 $pos2){
        $bbs = $this->getCollisionBoxes();
        if(empty($bbs)){
            return null;
        }

        /** @var MovingObjectPosition|null $currentHit */
        $currentHit = null;
        /** @var int|float $currentDistance */
        $currentDistance = PHP_INT_MAX;

        foreach($bbs as $bb){
            $nextHit = $bb->calculateIntercept($pos1, $pos2);
            if($nextHit === null){
                continue;
            }

            $nextDistance = $nextHit->hitVector->distanceSquared($pos1);
            if($nextDistance < $currentDistance){
                $currentHit = $nextHit;
                $currentDistance = $nextDistance;
            }
        }

        if($currentHit !== null){
            $currentHit->blockX = $this->x;
            $currentHit->blockY = $this->y;
            $currentHit->blockZ = $this->z;
        }

        return $currentHit;
    }

	public function setMetadata(string $metadataKey, MetadataValue $metadataValue){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->setMetadata($this, $metadataKey, $metadataValue);
		}
	}

	public function getMetadata(string $metadataKey){
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->getMetadata($this, $metadataKey);
		}

		return null;
	}

	public function hasMetadata(string $metadataKey) : bool{
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->hasMetadata($this, $metadataKey);
		}
		return true;
	}

	public function removeMetadata(string $metadataKey, Plugin $plugin){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->removeMetadata($this, $metadataKey, $plugin);
		}
	}
	
	/**
	 * Returns the 4 blocks on the horizontal axes around the block (north, south, east, west)
	 *
	 * @return Block[]
	 */
	public function getHorizontalSides() : array{
		return [
			$this->getSide(Vector3::SIDE_NORTH),
			$this->getSide(Vector3::SIDE_SOUTH),
			$this->getSide(Vector3::SIDE_WEST),
			$this->getSide(Vector3::SIDE_EAST)
		];
	}

	/**
	 * Returns the six blocks around this block.
	 *
	 * @return Block[]
	 */
	public function getAllSides() : array{
		return array_merge(
			[
				$this->getSide(Vector3::SIDE_DOWN),
				$this->getSide(Vector3::SIDE_UP)
			],
			$this->getHorizontalSides()
		);
	}

    /**
     * Bitmask to use to remove superfluous information from block meta when getting its item form or name.
     * This defaults to -1 (don't remove any data). Used to remove rotation data and bitflags from block drops.
     *
     * If your block should not have any meta value when it's dropped as an item, override this to return 0 in
     * descendent classes.
     *
     * @return int
     */
    public function getVariantBitmask() : int{
        return -1;
    }

    /**
     * Returns the block meta, stripped of non-variant flags.
     * @return int
     */
    public function getVariant() : int{
        return $this->meta & $this->getVariantBitmask();
    }

    /**
     * @return Item
     */
    public function getPickedItem() : Item{
        return Item::get($this->getItemId(), $this->getVariant());
    }

    /**
     * Returns the time in ticks which the block will fuel a furnace for.
     * @return int
     */
    public function getFuelTime() : int{
        return 0;
    }
}
