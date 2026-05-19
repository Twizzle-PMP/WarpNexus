<?php

declare(strict_types=1);

namespace Twizzle\WarpNexus\warp;

use pocketmine\utils\Config;

class WarpManager {

    private array $warps = [];

    public function __construct(private Config $config) {
        $this->load();
    }

    private function load(): void {
        foreach ($this->config->getAll() as $name => $data) {
            $warp = Warp::deserialize($data);
            if ($warp !== null) {
                $this->warps[$name] = $warp;
            }
        }
    }

    public function save(): void {
        $data = [];
        foreach ($this->warps as $name => $warp) {
            $data[$name] = $warp->serialize();
        }
        $this->config->setAll($data);
        $this->config->save();
    }

    public function addWarp(Warp $warp): void {
        $this->warps[$warp->getName()] = $warp;
        $this->save();
    }

    public function removeWarp(string $name): bool {
        if (!isset($this->warps[$name])) return false;
        unset($this->warps[$name]);
        $this->save();
        return true;
    }

    public function getWarp(string $name): ?Warp {
        return $this->warps[$name] ?? null;
    }

    public function getWarps(): array {
        return $this->warps;
    }

    public function isSlotTaken(int $slot, ?string $exclude = null): bool {
        foreach ($this->warps as $name => $warp) {
            if ($warp->getSlot() === $slot && ($exclude === null || $name !== $exclude)) {
                return true;
            }
        }
        return false;
    }
}
