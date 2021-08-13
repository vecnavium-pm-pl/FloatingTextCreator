<?php


namespace Vecnavium\FloatingTextCreator;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use Vecnavium\FloatingTextCreator\Commands\FTCCommand;
use Vecnavium\FloatingTextCreator\Tasks\ConfigVersionTask;
use Vecnavium\FloatingTextCreator\Tasks\FTCUpdateTask;
use Vecnavium\FloatingTextCreator\Utils\CustomFloatingText;

class Main extends PluginBase {

    /** @var array */
    public array $floatingTexts = [];
    /** @var Config|null */
    private Config $floatingText;
    /** @var ConfigVersionTask|null */
    private ConfigVersionTask $configVersion;

    public function onLoad(): void
    {
        $this->configVersion = new ConfigVersionTask($this);
    }

    public function onEnable(): void
    {
        $this->floatingText = new Config($this->getDataFolder() . "ftc.yml", Config::YAML);
        $this->getServer()->getCommandMap()->register("FloatingTextCreator", new FTCCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new FTCUpdateTask($this), 20 * $this->getUpdateTimer());
        $this->restartFTC();
    }
    
    public function restartFTC(): void {
        foreach($this->getFloatingTexts()->getAll() as $id => $array) {
            $this->floatingTexts[$id] = CustomFloatingText::create(Position::fromObject(new Vector3($array["x"], $array["y"], $array["z"]),
                $this->getServer()->getLevelByName($array['level'])));
        }
    }

    public function getFloatingTexts(): Config {
        return $this->floatingText;
    }

    public function getUpdateTimer(): int {
        return $this->getConfig()->get("ft-updatetime");
    }


    public function replaceProcess(Player $player, string $string): string {
        $string = str_replace("{player}", $player->getName(), $string);
        $string = str_replace("{ip}", Server::getInstance()->getIp(), $string);
        $string = str_replace("{port}", Server::getInstance()->getPort(), $string);
        $string = str_replace("{line}", TF::EOL, $string);
        $string = str_replace("\n", TF::EOL, $string);
        $string = str_replace("{world}", $player->getLevel()->getName(), $string);
        $string = str_replace("{x}", $player->getX(), $string);
        $string = str_replace("{y}", $player->getY(), $string);
        $string = str_replace("{z}", $player->getZ(), $string);
        $string = str_replace("{online}", Server::getInstance()->getQueryInformation()->getPlayerCount(), $string);
        $string = str_replace("{max_online}", Server::getInstance()->getQueryInformation()->getMaxPlayerCount(), $string);
        $string = str_replace("{ping}", $player->getPing(), $string);
        $string = str_replace("{tps}", Server::getInstance()->getTicksPerSecond(), $string);
        return $string;
    }

    /**
     * @return ConfigVersionTask
     */
    public function getConfigVersion(): ConfigVersionTask
    {
        return $this->configVersion;
    }

}
