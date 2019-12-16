<?php

class InvoiceItemListComponent extends HTMLList {

	protected function populateItem($itemOrder) {
		$item = self::getItem($itemOrder->getItemId());

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

		$this->addModel("is_item_price", array(
			"visible" => (!is_null($itemOrder->getItemPrice()) && (int)$itemOrder->getItemPrice() > 0)
		));
		$this->addModel("is_total_price", array(
			"visible" => (!is_null($itemOrder->getTotalPrice()) && (int)$itemOrder->getTotalPrice() > 0)
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
	private function getItem($itemId){

		try{
			return self::itemDao()->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function itemDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}
}
