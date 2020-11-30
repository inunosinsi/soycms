<?php

class InvoiceItemListComponent extends HTMLList {

	private $reducedTaxRateMode;	//軽減税率モード

	protected function populateItem($itemOrder) {

		$itemId = (is_numeric($itemOrder->getItemId())) ? (int)$itemOrder->getItemId() : 0;
		$item = soyshop_get_item_object($itemId);

		$this->addLink("item_id", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
			"link" => SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId())
		));

		$this->addLabel("item_code", array(
			"text" => ($itemId > 0) ? $item->getCodeOnAdmin() : ""
		));

		$itemName = $itemOrder->getItemNameOnAdmin();
		if($item->getIsDisabled() == SOYShop_Item::IS_DISABLED) $itemName = "<span style=\"font-weight:bold;color:red;\">" . $itemName . "(削除した商品)</span>";
		$this->addLabel("item_name", array(
			"html" => $itemName
		));

		//軽減税率の区分
		$this->addLabel("reduced_tax_rate_item", array(
			"text" => ($this->reducedTaxRateMode && ConsumptionTaxUtil::isReducedTaxRateItem($itemId)) ? "*" : ""
		));

		$this->addLabel("item_option", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? soyshop_build_item_option_html_on_item_order($itemOrder) : ""
		));

		$this->addLabel("item_count", array(
			"text" => (is_numeric($itemOrder->getItemCount())) ? number_format($itemOrder->getItemCount()) : ""
		));

		$this->addModel("is_item_price", array(
			"visible" => (is_numeric($itemOrder->getItemPrice()) && (int)$itemOrder->getItemPrice() > 0)
		));
		$this->addLabel("item_price", array(
			"text" => (is_numeric($itemOrder->getItemPrice())) ? number_format($itemOrder->getItemPrice()) : 0
		));

		$this->addLabel("item_total_price", array(
			"text" => (is_numeric($itemOrder->getTotalPrice())) ? number_format($itemOrder->getTotalPrice()) : 0
		));
	}

	function setReducedTaxRateMode($reducedTaxRateMode){
		$this->reducedTaxRateMode = $reducedTaxRateMode;
	}
}
