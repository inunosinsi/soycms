<?php

class InvoiceItemListComponent extends HTMLList {

	private $itemDao;

	protected function populateItem($itemOrder) {
		
		$item = $this->getItem($itemOrder->getItemId());

		$this->addLink("item_id", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
			"link" => SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId())
		));

		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));

		$this->addLabel("item_name", array(
			"text" => $itemOrder->getItemName()
		));
		
		SOYShopPlugin::load("soyshop.item.option");
		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $itemOrder,
		));
		
		$this->addLabel("item_option", array(
			"html" => $delegate->getHtmls()
		));

		$this->addLabel("item_count", array(
			"text" => $itemOrder->getItemCount()
		));

		$this->addLabel("item_price", array(
			"text" => number_format($itemOrder->getItemPrice())
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
			return $this->itemDao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>