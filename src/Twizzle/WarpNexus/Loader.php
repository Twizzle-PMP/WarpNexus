<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use muqsit\invmenu\InvMenuHandler;
use Twizzle\WarpNexus\warp\WarpManager;
use Twizzle\WarpNexus\command\WarpCommand;
use Twizzle\WarpNexus\command\SetWarpCommand;
use Twizzle\WarpNexus\command\DelWarpCommand;
use Twizzle\WarpNexus\session\EditSession;

class Loader extends PluginBase {

    private static self $instance;
    private WarpManager $warpManager;
    private Config $warpsConfig;

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("warps.json", false);
        
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        
        $this->warpsConfig = new Config($this->getDataFolder() . "warps.json", Config::JSON);
        $this->warpManager = new WarpManager($this->warpsConfig);
        EditSession::init();
        
        $this->getServer()->getCommandMap()->registerAll("warpnexus", [
            new WarpCommand($this),
            new SetWarpCommand($this),
            new DelWarpCommand($this)
        ]);
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getWarpManager(): WarpManager {
        return $this->warpManager;
    }
}