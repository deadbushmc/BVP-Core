<?php

declare(strict_types=1);

namespace Core\BVPCore;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\InventoryBase;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\level\Level;
use pocketmine\level\Position;
use jojoe77777\FormAPI\Form;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use JackMD\KDR\KDR;
use pocketmine\utils\Config;
use Inaayat\KillStreak\KillStreak;

class bvpcore extends PluginBase implements Listener {
    public $swordcooldown = [];
    public $settingscooldown = [];
    public $statscooldown = [];

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event){
        $this->getServer()->loadLevel($this->getConfig()->get("spawn"));
        $player = $event->getPlayer();
        $name = $player->getName();
        $x = $this->getConfig()->get("sx");
        $y = $this->getConfig()->get("sy");
        $z = $this->getConfig()->get("sz");
        $level = $this->getServer()->getLevelByName($this->getConfig()->get("spawn"));
        $pos = new Position($x , $y , $z , $level);
        $player->teleport($pos);
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $dsword = Item::get(276, 0, 1)->setCustomName($this->getConfig()->get("itemOne"));
        $player->getInventory()->setItem(0, $dsword);
        $settings = Item::get(345, 0, 1)->setCustomName($this->getConfig()->get("itemTwo"));
        $player->getInventory()->setItem(4, $settings);
        $stats = Item::get(340, 0, 1)->setCustomName($this->getConfig()->get("itemThree"));
        $player->getInventory()->setItem(8, $stats);
        $event->setJoinMessage($this->getConfig()->get("joinmsg") . " " . $name);
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        $cause = $event->getEntity()->getLastDamageCause();
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        if($event->getEntity() instanceof Player){
            $event->setDrops([]);
        }

        if($cause instanceof EntityDamageByEntityEvent){
            if($this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"))){
                $killer = $cause->getDamager();
                $killer->getInventory()->clearAll();
                $killer->getArmorInventory()->clearAll();
                $killer->removeAllEffects();
                $item1 = Item::get(276, 0, 1);
                $item1->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(9), 3));
                $killer->getInventory()->setItem(0, $item1);
                $item2 = Item::get(368, 0, 16);
                $killer->getInventory()->setItem(8, $item2);
                $item3 = Item::get(438, 22, 34);
                $killer->getInventory()->addItem($item3);
                $dh = Item::get(310, 0, 1);
                $dh->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
                $dh->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
                $killer->getArmorInventory()->setItem(0, $dh);
                $dc = Item::get(311, 0, 1);
                $dc->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
                $dc->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
                $killer->getArmorInventory()->setItem(1, $dc);
                $dl = Item::get(312, 0, 1);
                $dl->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
                $dl->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
                $killer->getArmorInventory()->setItem(2, $dl);
                $db = Item::get(313, 0, 1);
                $db->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
                $db->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
                $killer->getArmorInventory()->setItem(1, $db);$killer->getInventory()->clearAll();
                $event->setDeathMessage("§4" . $name . " §7 got slained by §2" . $killer->getName() . "[§4" . $killer->getHealth() . "§2]");
                $killer->setHealth(20);
                $player->setFood(20);
            }elseif($this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"))){

            }
        }elseif($cause->getCause() === EntityDamageEvent::CAUSE_FALL){
            $event->setCancelled(true);
        }elseif($cause->getCause() === EntityDamageEvent::CAUSE_VOID){
            $event->setDeathMessage("§4" . $name . "§7 got yeeted into void");
        }
    }

    public function onRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        $this->getServer()->loadLevel($this->getConfig()->get("spawn"));
        $x = $this->getConfig()->get("sx");
        $y = $this->getConfig()->get("sy");
        $z = $this->getConfig()->get("sz");
        $level = $this->getServer()->getLevelByName($this->getConfig()->get("spawn"));
        $pos = new Position($x , $y , $z , $level);
        $player->teleport($pos);
        $player->setHealth(20);
        $player->setFood(20);
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $dsword = Item::get(276, 0, 1)->setCustomName($this->getConfig()->get("itemOne"));
        $player->getInventory()->setItem(0, $dsword);
        $settings = Item::get(345, 0, 1)->setCustomName($this->getConfig()->get("itemTwo"));
        $player->getInventory()->setItem(4, $settings);
        $stats = Item::get(340, 0, 1)->setCustomName($this->getConfig()->get("itemThree"));
        $player->getInventory()->setItem(8, $stats);
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage($this->getConfig()->get("quitmsg") . " " . $name);
    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $itemname = $event->getPlayer()->getInventory()->getItemInHand()->getCustomName();
        $item1 = Item::get(276, 0, 1)->getCustomName($this->getConfig()->get("itemOne"));
        $item2 = Item::get(345, 0, 1)->getCustomName($this->getConfig()->get("itemTwo"));
        $item3 = Item::get(340, 0, 1)->getCustomName($this->getConfig()->get("itemThree"));
        if ($itemname === $this->getConfig()->get("itemOne")){
            $player = $event->getPlayer();
            if(!isset($this->swordcooldown[$player->getName()])){
                $this->swordcooldown[$player->getName()] = time() + 2;
                    $this->ffa($player);
            }else{
                if(time() < $this->swordcooldown[$player->getName()]){
                    $remaining = $this->swordcooldown[$player->getName()] - time();
                 $player->sendPopup("§4Cooldown " . $remaining);                          
              }else{
                unset($this->swordcooldown[$player->getName()]);
              }
            }
        }elseif($itemname === $this->getConfig()->get("itemTwo")){
            $player = $event->getPlayer();
            if(!isset($this->settingscooldown[$player->getName()])){
                $this->settingscooldow[$player->getName()] = time() + 2;
                    $player->sendMessage("§l§2Coming Soon....");
            }else{
                if(time() < $this->settingscooldow[$player->getName()]){
                    $remaining = $this->settingscooldow[$player->getName()] - time();
                 $player->sendPopup("§4Cooldown " . $remaining);                          
              }else{
                unset($this->settingscooldow[$player->getName()]);
              }
            }
        }elseif($itemname === $this->getConfig()->get("itemThree")){
            $player = $event->getPlayer();
            if(!isset($this->statscooldown[$player->getName()])){
                $this->statscooldown[$player->getName()] = time() + 2;
                    $this->stats($player);
            }else{
                if(time() < $this->statscooldown[$player->getName()]){
                    $remaining = $this->statscooldown[$player->getName()] - time();
                 $player->sendPopup("§4Cooldown " . $remaining);                          
              }else{
                unset($this->statscooldown[$player->getName()]);
              }
            }
        }
    }

    public function ffa($player){
        $debufflevel = $this->getConfig()->get("nodebuff");
        $this->getServer()->loadLevel($debufflevel);
        $form = new SimpleForm(function (Player $player, int $data = null){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                    $this->nodebuff($player);
                break;
            }
        });
        $form->setTitle($this->getConfig()->get("ffaTitle"));
        $form->addButton("§7Nodebuff\n§l§3» §r§bCurrently playing: §f" . count($this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"))->getPlayers()));
        $player->sendForm($form);
        return $form;
    }

    public function stats($player){
        $form = new SimpleForm(function (Player $player, int $data = null){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                break;

                case 1:
                break;

                case 2:
                break;
            }
        });
        $form->setTitle($this->getConfig()->get("statsTitle"));
        $form->addButton("§7Total Kills\n§l§3» §r§b" . KDR::getInstance()->getProvider()->getPlayerKillPoints($player) . " Kills");
        $form->addButton("§7Total Deaths\n§l§3» §r§b" . KDR::getInstance()->getProvider()->getPlayerDeathPoints($player) . " Deaths");
        $form->addButton("§7Total Streak\n§l§3» §r§b" . KillStreak::getInstance()->getProvider()->getPlayerKSPoints($player) . " Kill Streak");
        $player->sendForm($form);
        return $form;
    }

    public function nodebuff($player){
        $this->getServer()->loadLevel($this->getConfig()->get("nodebuff"));
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        switch(random_int(0, 4)){
            case 0:
                $x = $this->getConfig()->get("onenx");
                $y = $this->getConfig()->get("oneny");
                $z = $this->getConfig()->get("onenz");
                $level = $this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"));
                $pos = new Position($x , $y , $z , $level);
                $player->teleport($pos);
            break;

            case 1:
                $x = $this->getConfig()->get("twonx");
                $y = $this->getConfig()->get("twony");
                $z = $this->getConfig()->get("twonz");
                $level = $this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"));
                $pos = new Position($x , $y , $z , $level);
                $player->teleport($pos);
            break;

            case 2:
                $x = $this->getConfig()->get("threenx");
                $y = $this->getConfig()->get("threeny");
                $z = $this->getConfig()->get("threenz");
                $level = $this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"));
                $pos = new Position($x , $y , $z , $level);
                $player->teleport($pos);
            break;

            case 3:
                $x = $this->getConfig()->get("fournx");
                $y = $this->getConfig()->get("fourny");
                $z = $this->getConfig()->get("fournz");
                $level = $this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"));
                $pos = new Position($x , $y , $z , $level);
                $player->teleport($pos);
            break;

            case 4:
                $x = $this->getConfig()->get("fivenx");
                $y = $this->getConfig()->get("fiveny");
                $z = $this->getConfig()->get("fivenz");
                $level = $this->getServer()->getLevelByName($this->getConfig()->get("nodebuff"));
                $pos = new Position($x , $y , $z , $level);
                $player->teleport($pos);
            break;
        }
        $item1 = Item::get(276, 0, 1);
        $item1->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(9), 3));
        $player->getInventory()->setItem(0, $item1);
        $item2 = Item::get(368, 0, 16);
        $player->getInventory()->setItem(8, $item2);
        $item3 = Item::get(438, 22, 34);
        $player->getInventory()->addItem($item3);
        $dh = Item::get(310, 0, 1);
        $dh->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
        $dh->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
        $player->getArmorInventory()->setItem(0, $dh);
        $dc = Item::get(311, 0, 1);
        $dc->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
        $dc->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
        $player->getArmorInventory()->setItem(1, $dc);
        $dl = Item::get(312, 0, 1);
        $dl->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
        $dl->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
        $player->getArmorInventory()->setItem(2, $dl);
        $db = Item::get(313, 0, 1);
        $db->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17), 3));
        $db->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
        $player->getArmorInventory()->setItem(3, $db);
    }
}
