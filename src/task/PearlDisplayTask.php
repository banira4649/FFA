<?php

declare(strict_types=1);

namespace banira4649\FFA\task;

use banira4649\FFA\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PearlDisplayTask extends Task{

    public function onRun(): void{
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if($player->getWorld() !== Main::$stage) continue;
            if(!Main::getPearlUsedTime($player)){
                $player->getXpManager()->setXpLevel(0);
                $player->getXpManager()->setXpProgress(0.0);
            }else{
                $remaining = Main::getPearlRemaining($player);
                if($remaining <= 0){
                    $player->getXpManager()->setXpLevel(0);
                    $player->getXpManager()->setXpProgress(0.0);
                }else{
                    $player->getXpManager()->setXpLevel((int)$remaining);
                    $player->getXpManager()->setXpProgress($remaining / 15);
                }
            }
        }
    }
}