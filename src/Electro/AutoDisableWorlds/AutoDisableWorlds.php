<?php

namespace Electro\AutoDisableWorlds;

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

    public function onLevelChange(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();

        if (!$player instanceof Player)
        {
            return;
        }

        $fromWorld = $event->getFrom()->getWorld();
        if (count($fromWorld->getPlayers()) - 1 < 1 && $fromWorld !== $this->getServer()->getWorldManager()->getDefaultWorld())
        {
            if ($this->whitelist && in_array($fromWorld->getFolderName(), $this->worlds))
            {
                $this->getScheduler()->scheduleDelayedTask(new WorldDisableTask($this, $fromWorld), 10);
            }
            if (!$this->whitelist && !in_array($fromWorld->getFolderName(), $this->worlds))
            {
                $this->getScheduler()->scheduleDelayedTask(new WorldDisableTask($this, $fromWorld), 10);
            }
        }
    }

    public function unloadWorld($world)
    {
        $this->getServer()->getWorldManager()->unloadWorld($world);
    }
}