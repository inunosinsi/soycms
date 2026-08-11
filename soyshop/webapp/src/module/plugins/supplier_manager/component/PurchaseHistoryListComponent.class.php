<?php

class PurchaseHistoryListComponent extends HTMLList {

	protected function populateItem($entity, $idx) {
		$this->addLabel("log_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getLogDate())
		));

		$item = soyshop_get_item_object($entity->getItemId());
		$parent = soyshop_get_item_object($item->getType());
		$this->addLink("item_name", array(
			"link" => SOY2PageController::createLink("Extension.Detail.purchase_slip_manager." . $parent->getId()),
			"text" => $parent->getOpenItemName()
		));

		$this->addLabel("stock", array(
			"text" => number_format($entity->getStock())
		));
	}
}
