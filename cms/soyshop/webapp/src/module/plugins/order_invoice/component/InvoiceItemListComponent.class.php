<?php

class InvoiceItemListComponent extends HTMLList {

	protected function populateItem($itemOrder) {

		$item = self::getItem($itemOrder->getItemId());

		$this->addLink("item_id", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
			"link" => SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId())
		));

		$this->addLabel("item_code", array(
			"text" => ((int)$itemOrder->getItemId() > 0) ? $item->getCode() : ""
		));

		$itemName = $itemOrder->getItemName();
		if($item->getIsDisabled() == SOYShop_Item::IS_DISABLED) $itemName = "<span style=\"font-weight:bold;color:red;\">" . $itemName . "(削除した商品)</span>";
		$this->addLabel("item_name", array(
			"html" => $itemName
		));

		$this->addLabel("item_option", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? soyshop_build_item_option_html_on_item_order($itemOrder) : ""
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
}
