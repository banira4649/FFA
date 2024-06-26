<?php

declare(strict_types=1);

namespace banira4649\FFA\commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use banira4649\FFA\Main;

class FFACommand extends Command{

    public function __construct(string $name){
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, "FFAステージに入場します");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if($sender instanceof Player){
            Main::joinStage($sender);
        }
        return true;
    }

}
