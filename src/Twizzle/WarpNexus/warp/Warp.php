<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\warp;

use pocketmine\Server;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;

class Warp {

    public function __construct(
        private string $name,
        private string $displayName,
        private Location $position,
        private string $itemId,
        private int $slot,
        private array $lore = []
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function setDisplayName(string $name): void {
        $this->displayName = $name;
    }

    public function getPosition(): Location {
        return $this->position;
    }

    public function setPosition(Location $position): void {
        $this->position = $position;
    }

    public function getItem(): Item {
        $item = StringToItemParser::getInstance()->parse($this->itemId);
        if ($item === null) {
            $item = VanillaItems::ENDER_PEARL();
        }
        $item->setCustomName($this->displayName);
        $item->setLore($this->lore);
        return $item;
    }

    public function getItemId(): string {
        return $this->itemId;
    }

    public function setItemId(string $id): void {
        $this->itemId = $id;
    }

    public function getSlot(): int {
        return $this->slot;
    }

    public function setSlot(int $slot): void {
        $this->slot = $slot;
    }

    public function getLore(): array {
        return $this->lore;
    }

    public function setLore(array $lore): void {
        $this->lore = $lore;
    }

    public function serialize(): array {
        return [
            "name" => $this->name,
            "displayName" => $this->displayName,
            "world" => $this->position->getWorld()->getFolderName(),
            "x" => $this->position->getX(),
            "y" => $this->position->getY(),
            "z" => $this->position->getZ(),
            "yaw" => $this->position->getYaw(),
            "pitch" => $this->position->getPitch(),
            "itemId" => $this->itemId,
            "slot" => $this->slot,
            "lore" => $this->lore
        ];
    }

    public static function deserialize(array $data): ?self {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
        if ($world === null) return null;
        
        $pos = new Location($data["x"], $data["y"], $data["z"], $world, $data["yaw"] ?? 0.0, $data["pitch"] ?? 0.0);
        
        return new self(
            $data["name"],
            $data["displayName"],
            $pos,
            $data["itemId"] ?? "ENDER_PEARL",
            $data["slot"] ?? 0,
            $data["lore"] ?? []
        );
    }
}