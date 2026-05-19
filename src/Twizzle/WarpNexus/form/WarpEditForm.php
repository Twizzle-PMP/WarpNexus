<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\form;

use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use Twizzle\WarpNexus\Loader;
use Twizzle\WarpNexus\warp\Warp;

class WarpEditForm {

    public static function openList(Player $player): void {
        $plugin = Loader::getInstance();
        $manager = $plugin->getWarpManager();
        $warps = array_values($manager->getWarps());
        
        if (count($warps) === 0) {
            $player->sendMessage($plugin->getConfig()->get("no-warps"));
            return;
        }

        $form = new SimpleForm(function (Player $player, ?int $data) use ($warps): void {
            if ($data === null) return;
            if (isset($warps[$data])) {
                self::open($player, $warps[$data]);
            }
        });
        
        $form->setTitle("  §l§dSelect §5W§da§5r§dp  ");
        $form->setContent("§dChoose a warp to edit:");
        
        foreach ($warps as $warp) {
            $form->addButton($warp->getDisplayName());
        }

        $player->sendForm($form);
    }

    public static function open(Player $player, Warp $warp): void {
        $plugin = Loader::getInstance();
        $manager = $plugin->getWarpManager();
        
        $slotOptions = array_map('strval', range(0, 53));
        
        $form = new CustomForm(function (Player $player, ?array $data) use ($warp, $manager, $plugin): void {
            if ($data === null) return;
            
            $warp->setDisplayName($data[0]);
            $warp->setLore(explode("|", $data[1]));
            
            $newSlot = (int) $data[2];
            if ($newSlot !== $warp->getSlot()) {
                if ($manager->isSlotTaken($newSlot, $warp->getName())) {
                    $player->sendMessage($plugin->getConfig()->get("warp-slot-taken"));
                    return;
                }
                $warp->setSlot($newSlot);
            }

            $material = trim($data[3]);
            if ($material !== "") {
                $parsed = StringToItemParser::getInstance()->parse($material);
                if ($parsed !== null) {
                    $warp->setItemId($material);
                }
            }

            if ((bool) $data[4]) {
                $warp->setPosition($player->getLocation());
            }

            $manager->save();
            $player->sendMessage(str_replace("{name}", $warp->getName(), $plugin->getConfig()->get("warp-updated")));
        });
        
        $form->setTitle("  §l§dEdit §5W§da§5r§dp  ");
        $form->addInput("Display Name", "Warp name...", $warp->getDisplayName());
        $form->addInput("Lore", "Line 1 | Line 2 | Line 3", implode("|", $warp->getLore()));
        $form->addDropdown("Menu Slot", $slotOptions, $warp->getSlot());
        $form->addInput("Item Material", "ENDER_PEARL, DIAMOND_SWORD...", $warp->getItemId());
        $form->addToggle("Update position to current", false);

        $player->sendForm($form);
    }
}