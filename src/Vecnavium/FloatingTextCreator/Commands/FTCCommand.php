<?php
declare(strict_types=1);

namespace Vecnavium\FloatingTextCreator\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Vecnavium\FloatingTextCreator\Main;

class FTCCommand extends Command
{

    private Main $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("ftc", "FloatingTextCreator Commands", "/ftc");
        $this->setPermission("ftc.command");
        $this->plugin = $plugin;
    }

    // Lazy to make the commands in separate files. Will have them separate soon
    //Pull requests are accepted and will be appreciated
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $texts = (array)$this->getPlugin()->getConfig()->get("ft-texts");
        $floatingTexts = (array)$this->getPlugin()->getFloatingTexts()->getAll();
        if (!$this->testPermission($sender)) {
            return false;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(TF::RED . "FloatingTextCreator Commands ");
            $sender->sendMessage(TF::WHITE . "/ftc create txt");
            $sender->sendMessage(TF::WHITE . "/ftc del");
            return false;
        }
        switch ($args[0]) {
            // ftc Delete command
            case "del":
                if (!isset($args[1])) {
                    $sender->sendMessage(TF::YELLOW . "Usage: /ftc remove {id} of the text");
                    return false;
                }
                if (!isset($floatingTexts[$args[1]])) {
                    $sender->sendMessage(TF::DARK_RED . "FloatingText with ID " . TF::YELLOW . $args[1] . TF::DARK_RED . " does not exist");
                    return false;
                }
                $this->getPlugin()->getServer()->getLevelByName($this->getPlugin()->getFloatingTexts()->getNested("$args[1].level"));
                $ft = $this->getPlugin()->floatingTexts[$args[1]];
                $ft->setText("");
                $this->getPlugin()->getFloatingTexts()->remove($args[1]);
                $this->getPlugin()->getFloatingTexts()->save();
                unset($this->getPlugin()->floatingTexts[$args[1]]);
                $sender->sendMessage(TF::DARK_RED . "You have removed the FT ID: [ " . TF::WHITE . $args[1] . TF::DARK_RED .  "]");
                break;
            default:
                $sender->sendMessage(TF::WHITE . "FloatingTextCreator Commands");
                $sender->sendMessage(TF::RED . "/ftc create txt");
                $sender->sendMessage(TF::RED . "/ftc del");
                break;

            // ftc Create Command

            case "create":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TF::RED . "You can not execute this command in console.");
                    return false;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage(TF::WHITE . "/ftc create txt {TxtName}");
                    return false;
                }
                switch ($args[1]) {
                    case "txt":
                        if (!isset($args[2])) {
                            $sender->sendMessage(TF::DARK_RED . "Usage: /ftc create txt {TxtName}");
                            return false;
                        }
                        if (!isset($texts[$args[2]])) {
                            $sender->sendMessage(TF::YELLOW . $args[2] . TF::DARK_RED . " does not exist in the config.yml.");
                            $sender->sendMessage(TF::YELLOW . $args[2] . TF::DARK_RED . " This is not a plugin bug! This is more than likely because you are doing something wrong.");
                            return false;
                        }
                        $id = rand(1, 1000) + rand(1, 1000);
                        $info = array(
                            $sender->getLocation()->asPosition(),
                            "level" => $sender->getWorld()->getFolderName(),
                            "text" => implode("{line}", $texts[$args[2]])
                        );
                        $this->getPlugin()->getFloatingTexts()->setNested("$id", $info);
                        $this->getPlugin()->getFloatingTexts()->save();
                        $this->getPlugin()->restartFTC();
                        $sender->sendMessage(TF::DARK_RED . "FloatingText spawned with a ID: " . TF::WHITE . $id);
                        break;
                    default:
                        $sender->sendMessage(TF::YELLOW . "Usage: /ftc create txt {TxtName}");
                        break;
                }
                break;
        }
        return true;
    }

    /**
     * @return Main
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}
