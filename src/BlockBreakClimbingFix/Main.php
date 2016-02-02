<?php

namespace BlockBreakClimbingFix;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\network\protocol\MovePlayerPacket;
class Main extends PluginBase implements Listener{
	
	public $positions = array();

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function onReceive(DataPacketReceiveEvent $event){
		$p = $event->getPlayer();
		$pk = $event->getPacket();
		if($pk instanceof PlayerActionPacket){
			if($pk->action === PlayerActionPacket::ACTION_START_BREAK){
				$this->positions[$p->getName()] = $p->getLocation();
			}
		}
	}
	
	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event){
		if($event->isCancelled()){
			$p = $event->getPlayer();
			if(isset($this->positions[$p->getName()])){
				$pos = $this->positions[$p->getName()];
				$this->revert($p,$pos,$pos->yaw,$pos->pitch);
			}
		}
	}
	
	public function revert($player, $pos, $yaw = null, $pitch = null, $mode = 0){
		$pk = new MovePlayerPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y + $player->getEyeHeight();
		$pk->z = $pos->z;
		$pk->bodyYaw = $yaw;
		$pk->pitch = $pitch;
		$pk->yaw = $yaw;
		$pk->mode = $mode;
		$pk->eid = 0;
		$player->dataPacket($pk);
	}
}
