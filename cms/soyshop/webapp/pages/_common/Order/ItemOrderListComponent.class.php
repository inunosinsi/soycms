<?php

SOYShopPlugin::load("soyshop.item.option");
class ItemOrderListComponent extends HTMLList {

	private $itemDAO;

	protected function populateItem($itemOrder) {
		
		$item = self::getItem($itemOrder->getItemId());

		$itemExists = (method_exists($item, "getCode") && strlen($item->getCode()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCode() : "Deleted Item (ID=" . $itemOrder->getItemId() . ")",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId()) : "",
		));

		$this->addInput("index_hidden", array(
			"name" => "Item[" . $itemOrder->getId() . "]",
			"value" => $itemOrder->getId()
		));

		//item_idが0の場合は名前を表示する
		$this->addLabel("item_name", array(
			"text" => ((int)$itemOrder->getItemId() === 0 || strpos($item->getCode(), "_delete_") === false) ? $itemOrder->getItemName() : "---"
		));

		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $itemOrder,
		));

		$this->addLabel("item_option", array(
			"html" => $delegate->getHtmls()
		));

		$this->addLabel("item_price", array(
			"text" => is_numeric($itemOrder->getItemPrice()) ? number_format($itemOrder->getItemPrice()) : $itemOrder->getItemPrice()
		));

		$this->addLabel("item_count", array(
			"text" => number_format($itemOrder->getItemCount())
		));

		$this->addLabel("item_total_price", array(
			"text" => number_format($itemOrder->getTotalPrice())
		));
	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	private function getItem($itemId){
		static $items;
		if(is_null($items)) $items = array();
		if(!is_numeric($itemId)) return new SOYShop_Item();
		if(isset($items[$itemId])) return $items[$itemId];
		try{
			$items[$itemId] = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$items[$itemId] = new SOYShop_Item();
		}
		return $items[$itemId];
	}

	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
