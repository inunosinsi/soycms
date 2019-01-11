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

		$this->addLabel("item_option", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? self::getItemOptionHtml($itemOrder) : ""
		));

		$this->addLabel("item_count", array(
			"text" => $itemOrder->getItemCount()
		));

		$this->addModel("is_item_price", array(
			"visible" => (!is_null($itemOrder->getItemPrice()) && (int)$itemOrder->getItemPrice() > 0)
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
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		try{
			return $dao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function getItemOptionHtml($itemOrder){
		SOYShopPlugin::load("soyshop.item.option");
		$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $itemOrder,
		))->getHtmls();

		if(!count($htmls)) return "";

		$html = array();
		foreach($htmls as $h){
			if(!strlen($h)) continue;
			$html[] = $h;
		}

		return implode("<br>", $html);
	}
}
