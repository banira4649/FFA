<?php

declare(strict_types=1);

namespace banira4649\FFA\event;

use banira4649\FFA\Main;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\EnderPearl;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class EventListener implements Listener{

    public function onEntityDamageEvent(\pocketmine\event\entity\EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if($entity->getWorld() === Main::$stage){
            if($entity->getLocation()->asVector3()->distance(Main::$spawn) <= 10){
                $event->cancel();
            }
            if(($cause !== 1) && ($cause !== 2)){
                $event->cancel();
            }
        }
    }

    public function onEntityDeathEvent(\pocketmine\event\entity\EntityDeathEvent $event): void{
        $entity = $event->getEntity();
        if($entity->getWorld() === Main::$stage){
            if($entity instanceof Player){
                $entity->setSpawn(Main::$spawn);
                $event->setDrops([]);
            }
        }
    }

    public function onPlayerExhaustEvent(PlayerExhaustEvent $event): void{
        $player = $event->getPlayer();
        if($player->getWorld() === Main::$stage){
            $event->cancel();
        }
    }

    public function onPlayerItemUseEvent(\pocketmine\event\player\PlayerItemUseEvent $event): void{
        $player = $event->getPlayer();
        if($player->getWorld() === Main::$stage){
            if($player->getLocation()->asVector3()->distance(Main::$spawn) <= 10 && $player->getGamemode() !== GameMode::CREATIVE()){
                $event->cancel();
            }elseif($event->getItem() instanceof EnderPearl){
                $remaining = Main::getPearlRemaining($player);
                if(!$remaining){
                    Main::setPearlUsedTime($player, microtime(true));
                }elseif($remaining <= 0){
                    Main::setPearlUsedTime($player, microtime(true));
                }else{
                    $event->cancel();
                }
            }
        }
    }

    public function onPlayerRespawnEvent(\pocketmine\event\player\PlayerRespawnEvent $event): void{
        $player = $event->getPlayer();
        if($player->getWorld() === Main::$stage){
            Main::joinStage($player);
        }
    }

    public function onBlockBreakEvent(BlockBreakEvent $event): void{
        if($event->getPlayer()->hasFiniteResources()){
            $event->cancel();
        }
    }

    public function onBlockUpdateEvent(BlockUpdateEvent $event): void{
        if($event->getBlock()->getPosition()->getWorld() === Main::$stage){
            $event->cancel();
        }
    }
}