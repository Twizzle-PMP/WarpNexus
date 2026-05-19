<?php

declare(strict_types=1);

namespace muqsit\invmenu\type\graphic;

use muqsit\invmenu\type\graphic\network\InvMenuGraphicNetworkTranslator;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;

final class BlockInvMenuGraphic implements PositionedInvMenuGraphic{

	public function __construct(
		readonly private Block $block,
		readonly private Vector3 $position,
		readonly private ?InvMenuGraphicNetworkTranslator $network_translator = null,
		readonly private int $animation_duration = 0
	){}

	public function getPosition() : Vector3{
		return $this->position;
	}

	public function send(Player $player, ?string $name) : void{
		$session = $player->getNetworkSession();
		$converter = $session->getTypeConverter();
		
		$session->sendDataPacket(UpdateBlockPacket::create(
			BlockPosition::fromVector3($this->position), 
			$converter->getBlockTranslator()->internalIdToNetworkId($this->block->getStateId()), 
			UpdateBlockPacket::FLAG_NETWORK, 
			UpdateBlockPacket::DATA_LAYER_NORMAL
		));
	}

	public function sendInventory(Player $player, Inventory $inventory) : bool{
		return $player->setCurrentWindow($inventory);
	}

	public function remove(Player $player) : void{
		$network = $player->getNetworkSession();
		$world = $player->getWorld();
		
		foreach($world->createBlockUpdatePackets([$this->position]) as $packet){
			$network->sendDataPacket($packet);
		}
	}

	public function getNetworkTranslator() : ?InvMenuGraphicNetworkTranslator{
		return $this->network_translator;
	}

	public function getAnimationDuration() : int{
		return $this->animation_duration;
	}
}