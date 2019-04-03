<?php

namespace chaca142;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\Server;

use metowa1227\moneysystem\api\core\API;
use onebone\economyapi\EconomyAPI;
use MixCoinSystem\MixCoinSystem;
use hayao\main;
use MoneyPlugin\MoneyPlugin;

class PW extends PluginBase implements Listener
{

    const TAG = "§f[§aPayWarp§f]";

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(self::TAG . "§aPayWarpを読み込みました");
        if (!file_exists($this->getDataFolder())) {
            mkdir($this->getDataFOlder(), 0744, true);
        }
        $this->pw = new Config($this->getDataFolder() . "pw.yml", Config::YAML, array(
            "Pay" => 300,
            "Plugin" => "EconomyAPI",   ##現在対応しているのは EconomyAPI, MoneySystem, MoneyPlugin, LevelMoneySystem, MixCoinSystem のみです
        ));
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                $sender->sendMessage(self::TAG."/pw help");
                return false;
            } else {
                if ($args[0] == "add"){
                    if(!$sender->isOp()){
                        $sender->sendMessage(self::TAG."§c権限がありません");
                        return false;
                    }else{
                        if(!isset($args[1])){
                            $sender->sendMessage(self::TAG."/pw add <ワープ地点名>");
                            return false;
                        }else{
                            $x = $sender->x;
                            $y = $sender->y;
                            $z = $sender->z;
                            $level = $sender->getLevel()->getName();
                            if(!$this->pw->exists($args[1])){
                                $this->AddPW($args[1], $x, $y, $z, $level);
                                $sender->sendMessage(self::TAG."§aワープ先§e".$args[1]."§aを作成しました");
                                return false;
                            }else{
                                $sender->sendMessage(self::TAG."§cワープ先§e".$args[1]."§cは既に登録されてます");
                                return false;
                            }
                        }
                    }
                }
                if($args[0] == "del"){
                    if(!isset($args[1])){
                        $sender->sendMessage(self::TAG."/pw del <ワープ地点名>");
                        return false;
                    }else{
                        if($this->pw->exists($args[1])){
                            $this->DelPW($args[1]);
                            $sender->sendMessage(self::TAG."§aワープ先§e".$args[1]."§aを削除しました");
                            return false;
                        }else{
                            $sender->sendMessage(self::TAG."§cワープ先§e".$args[1]."§cは登録されていません");
                            return false;
                        }
                    }
                }
                if($args[0] == "list"){
                    $sender->sendMessage("§a---ワープ先一覧---");
                    foreach($this->pw->getAll() as $b=>$a){
                        $x = $this->pw->getAll()[$b]["x"];
                        $y = $this->pw->getAll()[$b]["y"];
                        $z = $this->pw->getAll()[$b]["z"];
                        $level = $this->pw->getAll()[$b]["level"];
                        $message = "§a".$b."§f(X:{$x} Y:{$y} Z:{$z} ワールド:{$level}";
                        return true;
                    }
                    $sender->sendMessage(" ");
                    return true;
                }
                if($args[0] == "help"){
                    if($sender->isOp()){
                        $sender->sendMessage(self::TAG."/pw <warp | add | del | list | payset> [Option]");
                        return false;
                    }else{
                        $sender->sendMessage(self::TAG."/pw warp <ワープ地点名>");
                        return false;
                    }
                }
                if($args[0] == "payset"){
                    if($sender->isOp()){
                        if(isset($args[1])){
                            $this->pw->set("Pay", $args[1]);
                            $this->pw->save();
                            return false;
                        }else{
                            $sender->sendMessage(self::TAG."/pw payset <値段>");
                            return false;
                        }
                    }else{
                        $sender->sendMessage(self::TAG."§c権限がありません");
                        return false;
                    }
                }
                if($args[0] == "warp"){
                    if(isset($args[1])){
                        if($this->pw->exists($args[1])){
                            $this->PW($sender, $args[1]);
                            return false;
                        }else{
                            $sender->sendMessage(self::TAG."§cそのワープ先は登録されてません");
                            return false;
                        }
                    }else{
                        $sender->sendMessage(self::TAG."/pw warp <ワープ地点名>");
                        return false;
                    }
                }
        }
        }
     return false;
    }

    public function PW($player, $warpname){
        if($this->pw->exists($warpname)){
            $levelname = $this->pw->getAll()[$warpname]["level"];
            if($this->getServer()->loadLevel($levelname)){
                $x = $this->pw->getAll()[$warpname]["x"];
                $y = $this->pw->getAll()[$warpname]["y"];
                $z = $this->pw->getAll()[$warpname]["z"];
                $level = $this->getServer()->getLevelByName($levelname);
                $pos = new Position($x, $y, $z, $level);
                $mp = $this->pw->get("Plugin");
                $money = $this->pw->get("Pay");
                if($mp == "EconomyAPI"){
                    $pm = EconomyAPI::getInstance()->myMoney($player);
                    if($pm >= $money){
                        EconomyAPI::getInstance()->reduceMoney($player, $money);
                        $player->sendTip("§c-{$money}");
                        $player->teleport($pos);
                        return false;
                    }else{
                        $player->sendMessage(self::TAG."§cお金が足りません");
                        return false;
                    }
                }
                if($mp == "MoneySystem"){
                    $pm = API::getInstance()->get($player);
                    if($pm >= $money){
                        API::getInstance()->reduce($player, $money);
                        $player->sendTip("§c-{$money}");
                        $player->teleport($pos);
                        return false;
                    }else{
                        $player->sendMessage(self::TAG."§cお金が足りません");
                        return false;
                    }
                }
                if($mp == "MixCoinSystem"){
                    $pm = MixCoinSystem::getInstance()->GetCoin($player);
                    if($pm >= $money){
                        MixCoinSystem::getInstance()->MinusCoin($player, $money);
                        $player->sendTip("§c-{$money}");
                        $player->teleport($pos);
                        return false;
                    }else{
                        $player->sendMessage(self::TAG."§cお金が足りません");
                        return false;
                    }
                }
                if($mp == "MoneyPlugin"){
                    $pm = MoneyPlugin::getInstance()->getMoney($player);
                    if($pm >= $money){
                        MoneyPlugin::getInstance()->removemoney($player, $money);
                        $player->sendTip("§c-{$money}");
                        $player->teleport($pos);
                        return false;
                    }else{
                        $player->sendMessage(self::TAG."§cお金が足りません");
                        return false;
                    }
                }
                if($mp == "LevelMoneySystem"){
                    $pm = main::getInstance()->getMoney($player);
                    if($pm >= $money){
                        main::getInstance()->removeMoney($player, $money);
                        $player->sendTip("§c-{$money}");
                        $player->teleport($pos);
                        return false;
                    }else{
                        $player->sendMessage(self::TAG."§cお金が足りません");
                        return false;
                    }
                }
                return true;
            }else{
                $player->sendMessage(self::TAG."§cワープ先§e{$levelname}§cは登録されてません");
                return false;
            }
        }else{
            return false;
        }
    }

    public function AddPW($warpname, $x, $y, $z, $level){
        if(isset($warpname) && isset($x) && isset($y) && isset($z) && isset($level)){
            $this->pw->set($warpname, array(
                "x"=>$x,
                "y"=>$y,
                "z"=>$z,
                "level"=>$level,
                "metadata"=>array()
            ));
            $this->pw->save();
            return true;
        }else{
            return false;
        }
    }

    public function DelPW($warpname){
        if($this->pw->exists($warpname)){
            $this->pw->remove($warpname);
            $this->pw->save();
            return true;
        }else{
            return false;
        }
    }
}