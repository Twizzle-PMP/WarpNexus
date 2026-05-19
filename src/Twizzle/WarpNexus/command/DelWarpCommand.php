<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Twizzle\WarpNexus\Loader;

class DelWarpCommand extends Command {

    public function __construct(private Loader $plugin) {
        parent::__construct("delwarp", "Delete a warp", "/delwarp <name>", []);
        $this->setPermission("warp.nexus.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!isset($args[0])) {
            $sender->sendMessage("§dUsage: /delwarp <name>");
            return false;
        }

        $name = $args[0];
        if ($this->plugin->getWarpManager()->removeWarp($name)) {
            $sender->sendMessage(str_replace("{name}", $name, $this->plugin->getConfig()->get("warp-deleted")));
        } else {
            $sender->sendMessage(str_replace("{name}", $name, $this->plugin->getConfig()->get("warp-not-found")));
        }
        return true;
    }
}
