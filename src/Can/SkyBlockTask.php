<?php

namespace Can;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class SkyBlockTask extends Task{

    public function __construct($plugin){
        $this->p = $plugin;
    }

    public function onRun(int $ticks){
        $lv = $this->p->getServer()->getLevels();
        foreach($lv as $l){
            if(count($l->getPlayers()) == 0 && $l != $this->p->getServer()->getDefaultLevel()){
                $this->p->getServer()->unloadLevel($l);
            }
        }
    }
}