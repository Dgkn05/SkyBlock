<?php

namespace Can;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\entity\Animal;

use pocketmine\plugin\PluginBase;
use pocketmine\{Player, Server};

class EventListener implements Listener{

    public function __construct(SkyBlock $plugin){
        $this->p = $plugin;
		
    }
    
	
    public function ortakBlokKir(BlockBreakEvent $e){
        $o = $e->getPlayer();
        $ladi = $o->getLevel()->getFolderName();
        $b = $e->getBlock();
        if(file_exists($this->p->getDataFolder() . "Adalar/".$ladi.".yml")){
            if($ladi == $o->getName()){
                $e->setCancelled(false);
            }else{
                $cfg = new Config($this->p->getDataFolder()."Adalar/".$ladi.".yml", Config::YAML);
                $cfg->reload();
                $ortaklar = $cfg->get("Ortaklar");
                if(@in_array($o->getName(), $ortaklar)){
                    $e->setCancelled(false);
                }elseif(!$o->isOp()){
                    $e->setCancelled(true);
                    $o->sendPopup("§cOrtağın olmadığı adaya dokunamazsın!");
                }
            }
        }else{
            
        }
    }
	
    public function ortakBlokYer(BlockPlaceEvent $e){
        $o = $e->getPlayer();
        $ladi = $o->getLevel()->getFolderName();
        if(file_exists($this->p->getDataFolder() . "Adalar/".$ladi.".yml")){
            if($ladi == $o->getName()){
                $e->setCancelled(false);
            }else{
                $cfg = new Config($this->p->getDataFolder()."Adalar/".$ladi.".yml", Config::YAML);
                $cfg->reload();
                $ortaklar = $cfg->get("Ortaklar");
                if(@in_array($o->getName(), $ortaklar)){
                    $e->setCancelled(false);
                }elseif(!$o->isOp()){
                    $e->setCancelled(true);
                    $o->sendPopup("§cOrtağın olmadığı adaya dokunamazsın!");
                }
            }
        }else{
            
        }
    }
    
    public function ortakTikla(PlayerInteractEvent $e){
        $o = $e->getPlayer();
        $ladi = $o->getLevel()->getFolderName();
        if(file_exists($this->p->getDataFolder() . "Adalar/".$ladi.".yml")){
            if($ladi == $o->getName()){
                $e->setCancelled(false);
            }else{
                $cfg = new Config($this->p->getDataFolder()."Adalar/".$ladi.".yml", Config::YAML);
                $cfg->reload();
                $ortaklar = $cfg->get("Ortaklar");
                if(@in_array($o->getName(), $ortaklar)){
                    $e->setCancelled(false);
                }elseif(!$o->isOp()){
                    $e->setCancelled(true);
                    $o->sendPopup("§cOrtağın olmadığı adaya dokunamazsın!");
                }
            }
        }else{
            
        }
    }
    
	public function Pvp(EntityDamageEvent $e){
        if($e instanceof EntityDamageByEntityEvent){
            if($e->getEntity() instanceof Player && $e->getDamager() instanceof Player){
                $g = $e->getEntity();
                $lada = $g->getLevel()->getName();
                if(file_exists($this->p->getDataFolder()."Adalar/".$lada.".yml")){
                    $e->setCancelled(true);
                }else{}
            }
        }
    }
	
}