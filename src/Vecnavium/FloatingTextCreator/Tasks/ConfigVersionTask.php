<?php

namespace Vecnavium\FloatingTextCreator\Tasks;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use Vecnavium\FloatingTextCreator\Main;

class ConfigVersionTask
{
    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->init();
    }

    private function init(){
        $config = $this->plugin->getConfig();
        if (!$config->exists("CONFIG_VERSION") || $config->get("CONFIG_VERSION") != 2){
            $this->plugin->getLogger()->critical("Your config version is outdated. Please delete the config.yml file and restart the server.");
            $this->plugin->getLogger()->critical("Another method is to rename the config.yml to whatever in case you have hard working configuration for it to then regenerate the updated version.");
        }
        $this->plugin->saveDefaultConfig();
    }
}