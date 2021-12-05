<?php

namespace Electro\AutoDisableWorlds;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;

class AutoDisableWorlds extends PluginBase implements Listener{

    public array $worlds = [];
    public bool $whitelist = true;

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if ($this->getConfig()->get("Mode") !== "Whitelist" && !$this->getConfig()->get("Mode") !== "whitelist")
        {
            $this->whitelist = false;
        }
        foreach ($this->getConfig()->get("Worlds") as $world)
        {
            $this->worlds[] = $world;
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();

        $this->checkIfShouldUnloadWorld($world);
    }

    public function onLevelChange(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();

        if (!$player instanceof Player)
        {
            return;
        }

        $fromWorld = $event->getFrom()->getWorld();
        $this->checkIfShouldUnloadWorld($fromWorld);
    }

    public function checkIfShouldUnloadWorld($world)
    {
        if (count($world->getPlayers()) - 1 < 1 && $world !== $this->getServer()->getWorldManager()->getDefaultWorld())
        {
            if (!$world->isLoaded()) return;
            if ($this->whitelist && in_array($world->getFolderName(), $this->worlds))
            {
                $this->getScheduler()->scheduleDelayedTask(new WorldDisableTask($this, $world), 10);
            }
            if (!$this->whitelist && !in_array($world->getFolderName(), $this->worlds))
            {
                $this->getScheduler()->scheduleDelayedTask(new WorldDisableTask($this, $world), 10);
            }
        }
    }

    public function unloadWorld($world)
    {
        if (!$world->isLoaded()) return;
        $this->getServer()->getWorldManager()->unloadWorld($world);
    }
}
