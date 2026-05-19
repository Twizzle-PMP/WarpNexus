<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\menu;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\utils\DyeColor;
use pocketmine\scheduler\ClosureTask;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use Twizzle\WarpNexus\Loader;
use Twizzle\WarpNexus\session\EditSession;

class WarpEditMenu {

    public static function open(Player $player): void {
        $plugin = Loader::getInstance();
        $manager = $plugin->getWarpManager();
        $warps = $manager->getWarps();

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($plugin->getConfig()->get("edit-menu-title"));
        
        $filler = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::PINK)->asItem();
        $filler->setCustomName("§r §d§o✦");
        
        for ($i = 0; $i < 54; $i++) {
            $menu->getInventory()->setItem($i, $filler);
        }

        foreach ($warps as $warp) {
            $slot = $warp->getSlot();
            if ($slot < 0 || $slot > 53) continue;
            
            $item = $warp->getItem();
            $lore = array_merge($warp->getLore(), ["", "§r§d§lClick to select", "§r§d§oThen click target slot"]);
            $item->setLore($lore);
            $menu->getInventory()->setItem($slot, $item);
        }

        $session = EditSession::get($player);

        $menu->setListener(function (InvMenuTransaction $transaction) use ($plugin, $manager, $session): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slotClicked = $transaction->getAction()->getSlot();

            $warpClicked = null;
            foreach ($manager->getWarps() as $name => $warp) {
                if ($warp->getSlot() === $slotClicked) {
                    $warpClicked = $warp;
                    break;
                }
            }

            if ($warpClicked !== null) {
                $selectedName = $session->getSelectedWarp();
                
                if ($selectedName === null) {
                    $session->setSelectedWarp($warpClicked->getName());
                    $player->sendMessage("§dSelected warp §d§l" . $warpClicked->getName() . " §r§d. Click another slot to move.");
                    return $transaction->discard();
                }

                if ($selectedName === $warpClicked->getName()) {
                    $session->setSelectedWarp(null);
                    $player->sendMessage("§dDeselected warp.");
                    return $transaction->discard();
                }

                $selectedWarp = $manager->getWarp($selectedName);
                if ($selectedWarp !== null) {
                    $oldSlot = $selectedWarp->getSlot();
                    $selectedWarp->setSlot($slotClicked);
                    $warpClicked->setSlot($oldSlot);
                    $manager->save();
                    
                    $session->setSelectedWarp(null);
                    $player->sendMessage("§dSwapped warps!");
                    
                    $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                        if ($player->isOnline()) self::open($player);
                    }), 1);
                }
                return $transaction->discard();
            }

            if ($session->getSelectedWarp() !== null) {
                $selectedWarp = $manager->getWarp($session->getSelectedWarp());
                if ($selectedWarp !== null) {
                    $selectedWarp->setSlot($slotClicked);
                    $manager->save();
                    $session->setSelectedWarp(null);
                    $player->sendMessage("§dMoved warp to slot §d§l" . ($slotClicked + 1) . " §r§d.");
                    
                    $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                        if ($player->isOnline()) self::open($player);
                    }), 1);
                }
                return $transaction->discard();
            }

            return $transaction->discard();
        });

        $menu->setInventoryCloseListener(function (Player $player) use ($session): void {
            $session->setSelectedWarp(null);
        });

        $menu->send($player);
    }
}