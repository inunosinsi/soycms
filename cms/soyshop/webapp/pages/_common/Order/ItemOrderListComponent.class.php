<?php

class ItemOrderListComponent extends HTMLList {

	private $itemDAO;

	protected function populateItem($itemOrder) {

		$item = $this->getItem($itemOrder->getItemId());

		$itemExists = (method_exists($item, "getCode") && strlen($item->getCode()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCode() : "Deleted Item (ID=" . $itemOrder->getItemId() . ")",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId()) : "",
		));

		$this->addLabel("item_name", array(
			"text" => $itemOrder->getItemName()
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
	function getItem($itemId){
		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		return $item;
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>