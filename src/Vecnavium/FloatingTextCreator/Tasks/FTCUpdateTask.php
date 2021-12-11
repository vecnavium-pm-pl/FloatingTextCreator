<?php
declare(strict_types=1);

namespace Vecnavium\FloatingTextCreator\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Vecnavium\FloatingTextCreator\Main;
use Vecnavium\FloatingTextCreator\Utils\CustomFloatingText;


class FTCUpdateTask extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main {
        return $this->plugin;
    }

    public function onRun(): void {
        foreach($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            foreach($this->getPlugin()->floatingTexts as $id => $ft) {
                /** @var CustomFloatingText|null $ft */
                if ($player->getWorld() !== $ft->getPosition()->getLevel() && $ft->isViewer($player)){
                    $ft->remove($player);
                }
                if ($player->getWorld() === $ft->getPosition()->getLevel() && !$ft->isViewer($player)){
                    $ft->spawn($player);
                }
                $text = $this->getPlugin()->getFloatingTexts()->getNested("$id.text");
                if($player->hasPermission("ftc.command")) {
                    $ft->update($this->getPlugin()->replaceProcess($player, $text) . TF::EOL . TF::RED . "The ID(Only people with the permission can see the ID): " . $id, $player);
                }else{
                    $ft->update($this->getPlugin()->replaceProcess($player, $text), $player);
                }

            }
        }
    }

}
