<?php

class InvoiceItemListComponent extends HTMLList {

	protected function populateItem($itemOrder) {
		$itemId = (is_numeric($itemOrder->getItemId())) ? (int)$itemOrder->getItemId() : 0;
		$item = soyshop_get_item_object($itemId);

		$this->addLink("item_id", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemId,
			"link" => SOY2PageController::createLink("Item.Detail." . $itemId)
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
			"text" => soy2_number_format($itemOrder->getItemCount())
		));

		$this->addModel("is_item_price", array(
			"visible" => (is_numeric($itemOrder->getItemPrice()) && (int)$itemOrder->getItemPrice() > 0)
		));
		$this->addModel("is_total_price", array(
			"visible" => (is_numeric($itemOrder->getTotalPrice()) && (int)$itemOrder->getTotalPrice() > 0)
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($itemOrder->getItemPrice())
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($itemOrder->getTotalPrice())
		));
	}
}
