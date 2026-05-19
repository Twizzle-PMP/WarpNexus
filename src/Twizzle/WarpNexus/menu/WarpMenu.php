<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\menu;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\utils\DyeColor;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\sound\EndermanTeleportSound;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use Twizzle\WarpNexus\Loader;
use Twizzle\WarpNexus\warp\Warp;

class WarpMenu {

    public static function open(Player $player): void {
        $plugin = Loader::getInstance();
        $manager = $plugin->getWarpManager();
        $warps = $manager->getWarps();
        
        if (count($warps) === 0) {
            $player->sendMessage($plugin->getConfig()->get("no-warps"));
            return;
        }

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($plugin->getConfig()->get("warp-menu-title"));
        
        $filler = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::MAGENTA)->asItem();
        $filler->setCustomName("§r §d§o✦");
        
        for ($i = 0; $i < 54; $i++) {
            $menu->getInventory()->setItem($i, $filler);
        }

        foreach ($warps as $warp) {
            $slot = $warp->getSlot();
            if ($slot < 0 || $slot > 53) continue;
            
            $item = $warp->getItem();
            $lore = array_merge($warp->getLore(), ["", "§r§d§oSlot: §r§d§l" . ($slot + 1)]);
            $item->setLore($lore);
            $menu->getInventory()->setItem($slot, $item);
        }

        $menu->setListener(function (InvMenuTransaction $transaction) use ($plugin, $manager): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            
            $warp = null;
            foreach ($manager->getWarps() as $w) {
                if ($w->getSlot() === $slot) {
                    $warp = $w;
                    break;
                }
            }
            
            if ($warp !== null) {
                self::teleport($player, $warp, $plugin);
            }
            
            return $transaction->discard();
        });

        $menu->send($player);
    }

    private static function teleport(Player $player, Warp $warp, Loader $plugin): void {
        $delay = (int) $plugin->getConfig()->get("teleport-delay", 3);
        $name = $warp->getDisplayName();
        $startPos = $player->getPosition()->asVector3();
        
        $player->sendMessage(str_replace(["{name}", "{seconds}"], [$name, (string)$delay], $plugin->getConfig()->get("warp-teleporting")));
        
        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $warp, $plugin, $startPos): void {
            if (!$player->isOnline()) return;
            
            if ($player->getPosition()->distanceSquared($startPos) > 1.0) {
                $player->sendMessage($plugin->getConfig()->get("teleport-cancelled"));
                return;
            }

            $player->teleport($warp->getPosition());
            $player->sendMessage(str_replace("{name}", $warp->getDisplayName(), $plugin->getConfig()->get("warp-teleported")));
            $player->getWorld()->addSound($player->getPosition(), new EndermanTeleportSound());
        }), $delay * 20);
    }
}