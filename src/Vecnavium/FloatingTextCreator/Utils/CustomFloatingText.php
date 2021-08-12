<?php
declare(strict_types=1);

namespace Vecnavium\FloatingTextCreator\Utils;

use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\utils\UUID;

class CustomFloatingText
{
    /** @var int */
    private int $eid;
    /** @var string */
    private string $text = "";
    /** @var Position */
    private Position $position;
    /** @var Player[] */
    private array $viewers = [];

    /**
     * CustomFloatingText constructor.
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        $this->position = $position;
        $this->eid = Entity::$entityCount++;
    }

    public static function create(Position $position): CustomFloatingText
    {
        return new self($position);
    }

    /**
     * @param Player $player
     */
    public function spawn(Player $player): void
    {
        $pk = new AddPlayerPacket();
        $pk->entityRuntimeId = $this->eid;
        $pk->uuid = UUID::fromRandom();
        $pk->username = $this->text;
        $pk->entityUniqueId = $this->eid;
        $pk->position = $this->position->asVector3();
        $pk->item = ItemStackWrapper::legacy(ItemFactory::get(BlockIds::AIR, 0, 0));
        $flags =
            1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG |
            1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG |
            1 << Entity::DATA_FLAG_IMMOBILE;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
        ];
        $player->sendDataPacket($pk);
        $this->viewers[$player->getName()] = $player;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = $this->eid;
        $pk->metadata = [
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $text
            ]
        ];
        foreach ($this->viewers as $player){
            $player->sendDataPacket($pk);
        }
    }

    /**
     * @param Player $player
     */
    public function remove(Player $player): void
    {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->eid;
        $player->sendDataPacket($pk);
        if (isset($this->viewers[$player->getName()])) unset($this->viewers[$player->getName()]);
    }

    public function isViewer(Player $player): bool
    {
        return isset($this->viewers[$player->getName()]);
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->eid;
    }

}
