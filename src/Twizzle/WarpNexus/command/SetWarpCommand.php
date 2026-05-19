<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Twizzle\WarpNexus\Loader;
use Twizzle\WarpNexus\warp\Warp;

class SetWarpCommand extends Command {

    public function __construct(private Loader $plugin) {
        parent::__construct("setwarp", "Create a new warp", "/setwarp <name>", []);
        $this->setPermission("warp.nexus.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§dUse in-game.");
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage("§dUsage: /setwarp <name>");
            return false;
        }

        $name = $args[0];
        $manager = $this->plugin->getWarpManager();
        
        if ($manager->getWarp($name) !== null) {
            $sender->sendMessage(str_replace("{name}", $name, $this->plugin->getConfig()->get("warp-exists")));
            return false;
        }

        $slot = 0;
        while ($manager->isSlotTaken($slot)) {
            $slot++;
            if ($slot > 53) {
                $sender->sendMessage("§dNo free slots available.");
                return false;
            }
        }
        
        $warp = new Warp(
            $name,
            "§r§d§l" . $name,
            $sender->getLocation(),
            "ENDER_PEARL",
            $slot,
            ["§r§d§oClick to teleport!"]
        );

        $manager->addWarp($warp);
        $sender->sendMessage(str_replace("{name}", $name, $this->plugin->getConfig()->get("warp-created")));
        return true;
    }
}