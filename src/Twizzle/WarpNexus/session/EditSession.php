<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\session;

use pocketmine\player\Player;

class EditSession {

    private static array $sessions = [];

    public static function init(): void {
        self::$sessions = [];
    }

    public static function get(Player $player): self {
        $uuid = $player->getUniqueId()->toString();
        if (!isset(self::$sessions[$uuid])) {
            self::$sessions[$uuid] = new self();
        }
        return self::$sessions[$uuid];
    }

    public static function remove(Player $player): void {
        unset(self::$sessions[$player->getUniqueId()->toString()]);
    }

    private ?string $selectedWarp = null;

    public function getSelectedWarp(): ?string {
        return $this->selectedWarp;
    }

    public function setSelectedWarp(?string $name): void {
        $this->selectedWarp = $name;
    }
}
