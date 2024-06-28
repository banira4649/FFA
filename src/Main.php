<?php

declare(strict_types=1);

namespace banira4649\FFA;

use banira4649\FFA\event\EventListener;
use banira4649\FFA\event\player\PlayerJoinStageEvent;
use banira4649\FFA\task\PearlDisplayTask;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;
use pocketmine\world\{World, WorldManager};
use pocketmine\player\{Player, GameMode};
use pocketmine\item\{Item, PotionType, VanillaItems};
use pocketmine\item\enchantment\{VanillaEnchantments, EnchantmentInstance};
use pocketmine\entity\effect\{VanillaEffects, EffectInstance};
use banira4649\FFA\commands\{FFACommand};

class Main extends PluginBase{

    public const string WORLD_NAME = "ffa";
    public const int PEARL_COOLTIME = 15;

    public static WorldManager $worldManager;
    public static World $stage;
    public static Vector3 $spawn;

    /** @var array<string, float> */
    private static array $pearl = [];

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new FFACommand("ffa")
        ]);
        self::$worldManager = $this->getServer()->getWorldManager();
        self::$worldManager->loadWorld(self::WORLD_NAME);
        self::$stage = self::$worldManager->getWorldByName(self::WORLD_NAME);
        $stageData = new Config(
            Path::join($this->getServer()->getDataPath(), "worlds", self::WORLD_NAME, "config.json"),
            Config::JSON,
            ["spawn" => [0, 0, 0]]
        );
        self::$spawn = new Vector3(...$stageData->get("spawn"));
        $this->getScheduler()->scheduleRepeatingTask(new PearlDisplayTask(), 2);
    }

    private static function resetPlayer(Player $player): void{
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getXpManager()->setXpLevel(0);
        $player->getXpManager()->setXpProgress(0);
        $player->setOnFire(0);
        $player->setGameMode(GameMode::SURVIVAL());
        unset(self::$pearl[$player->getXuid()]);
    }

    private static function setInventory(Player $player): void{
        $inv = $player->getInventory();
        $ainv = $player->getArmorInventory();
        $items = [
            self::setEnchantments(
                VanillaItems::DIAMOND_SWORD()->setUnbreakable(),
                [
                    new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2),
                    new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 2)
                ]
            ),
            VanillaItems::ENDER_PEARL()->setCount(16),
            VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING())->setCount(34)
        ];
        $armors = [
            VanillaItems::DIAMOND_HELMET()->setUnbreakable(),
            VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(),
            VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(),
            VanillaItems::DIAMOND_BOOTS()->setUnbreakable()
        ];
        $inv->setContents($items);
        foreach($armors as $armor){
            $ainv->setItem($armor->getArmorSlot(), $armor->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        }
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 0x7fffffff, 0, true));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 0x7fffffff, 255, true));
    }

    public static function joinStage(Player $player): void{
        $ev = new PlayerJoinStageEvent($player);
        $ev->call();
        if(!$ev->isCancelled()){
            self::resetPlayer($player);
            self::setInventory($player);
            $player->teleport(self::$stage->getSafeSpawn());
            $player->teleport(self::$spawn);
            $player->sendMessage("§aFFAステージに入場しました");
        }else{
            $player->sendMessage("§cFFAステージへの入場に失敗しました");
        }
    }

    /**
     * @param EnchantmentInstance[] $enchantments
     */
    private static function setEnchantments(Item $item, array $enchantments): Item{
        foreach($enchantments as $enchantment){
            $item->addEnchantment($enchantment);
        }
        return $item;
    }

    public static function setPearlUsedTime(Player $player, float $microtime): void{
        self::$pearl[$player->getXuid()] = $microtime;
    }

    public static function getPearlUsedTime(Player $player): float|false{
        return self::$pearl[$player->getXuid()] ?? false;
    }

    public static function getPearlRemaining(Player $player): float|false{
        $used = self::getPearlUsedTime($player);
        return $used ? ($result = 15 - (microtime(true) - $used)) >= 0 ? $result : 0 : false;
    }
}
