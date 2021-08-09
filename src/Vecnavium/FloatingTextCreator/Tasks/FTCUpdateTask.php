<?php
declare(strict_types=1);

namespace Vecnavium\FloatingTextCreator\Tasks;

use pocketmine\utils\TextFormat as TF;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\scheduler\Task;
use pocketmine\math\Vector3;
use Vecnavium\FloatingTextCreator\Main;


class FTCUpdateTask extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main {
        return $this->plugin;
    }

    //Considering on rewriting this kind of a mess in my opinion. Only just wrote on autopilot(mind sense).
    //Again PRs are accepted
    public function onRun(int $currentTick): void {
        foreach($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            foreach($this->getPlugin()->floatingTexts as $id => $ft) {
                $text = $this->getPlugin()->getFloatingTexts()->getNested("$id.text");
                $level = $this->getPlugin()->getServer()->getLevelByName($this->getPlugin()->getFloatingTexts()->getNested("$id.level"));
                if($player->hasPermission("ftc.command.adm")) {
                    $ft->setText($this->getPlugin()->replaceProcess($player, $text) . TF::EOL . TF::RED . "The ID(Only people with the permission can see the ID): " . $id);
                }else{
                    $ft->setText($this->getPlugin()->replaceProcess($player, $text));
                }
                $level->addParticle($ft, [$player]);
            }
        }
    }

}
