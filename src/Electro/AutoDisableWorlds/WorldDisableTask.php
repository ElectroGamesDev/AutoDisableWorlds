<?php

namespace Electro\AutoDisableWorlds;

use Electro\AutoDisableWorlds\AutoDisableWorlds;
use pocketmine\scheduler\Task;

class WorldDisableTask extends Task{

    private $plugin;
    private $world;

    public function __construct(AutoDisableWorlds $plugin, $world){
        $this->plugin = $plugin;
        $this->world = $world;
    }

    public function onRun() : void
    {
        $this->plugin->unloadWorld($this->world);

    }
}
