<?php

declare(strict_types = 1);

namespace phoshp\viewerplus;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class ViewerPlus extends PluginBase implements Listener{
	public const VIEWER_SURVIVAL = 1;
	public const VIEWER_CREATIVE = 2;
	public const VIEWER_SURVIVAL_NO_CLIP = 3;

	/** @var int[] */
	private static $VIEWERS = [];

	public static function setViewer(Player $player, int $mode = self::VIEWER_SURVIVAL) : void{
		if($player->isSpectator()){
			self::removeViewer($player);
		}

		self::$VIEWERS[$player->getName()] = $mode;
		$player->setGamemode(3);
	}

	public static function removeViewer(Player $player, int $nextGameMode = Player::SURVIVAL) : void{
		unset(self::$VIEWERS[$player->getName()]);
		$player->setGamemode($nextGameMode);
	}

	public static function getViewerMode(Player $player) : ?int{
		return self::$VIEWERS[$player->getName()] ?? null;
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onSendPacket(DataPacketSendEvent $event) : void{
		$player = $event->getPlayer();
		$packet = $event->getPacket();

		if($mode = self::getViewerMode($player)){
			if($packet instanceof SetPlayerGameTypePacket){
				if($mode === self::VIEWER_SURVIVAL or $mode === self::VIEWER_SURVIVAL_NO_CLIP){
					$packet->gamemode = GameMode::SURVIVAL;
				}
			}elseif($packet instanceof AdventureSettingsPacket){
				if($mode === self::VIEWER_SURVIVAL or $mode === self::VIEWER_CREATIVE){
					$packet->setFlag(AdventureSettingsPacket::NO_CLIP, false);
				}
			}
		}
	}

	public function onReceivePacket(DataPacketReceiveEvent $event){
		$player = $event->getPlayer();
		$packet = $event->getPacket();

		if($mode = self::getViewerMode($player)){
			if($packet instanceof LevelSoundEventPacket){
				$event->setCancelled();
				$player->sendDataPacket($packet);
			}
		}
	}

	public function onDropItem(PlayerDropItemEvent $event) : void{
		$player = $event->getPlayer();

		if($mode = self::getViewerMode($player)){
			$event->setCancelled();
		}
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();

		if(isset(self::$VIEWERS[$player->getName()])){
			self::removeViewer($event->getPlayer());
		}
	}
}