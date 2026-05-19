<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Twizzle\WarpNexus\Loader;
use Twizzle\WarpNexus\menu\WarpMenu;
use Twizzle\WarpNexus\menu\WarpEditMenu;
use Twizzle\WarpNexus\form\WarpEditForm;

class WarpCommand extends Command {

    public function __construct(private Loader $plugin) {
        parent::__construct("warp", "Open warp menu", "/warp [edit|editmenu]", []);
        $this->setPermission("warp.nexus.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§dUse in-game.");
            return false;
        }

        if (isset($args[0]) && $args[0] === "editmenu" && $sender->hasPermission("warp.nexus.admin")) {
            WarpEditMenu::open($sender);
            return true;
        }

        if (isset($args[0]) && $args[0] === "edit" && $sender->hasPermission("warp.nexus.admin")) {
            if (!isset($args[1])) {
                WarpEditForm::openList($sender);
                return true;
            }
            $warp = $this->plugin->getWarpManager()->getWarp($args[1]);
            if ($warp === null) {
                $sender->sendMessage(str_replace("{name}", $args[1], $this->plugin->getConfig()->get("warp-not-found")));
                return false;
            }
            WarpEditForm::open($sender, $warp);
            return true;
        }

        WarpMenu::open($sender);
        return true;
    }
}
