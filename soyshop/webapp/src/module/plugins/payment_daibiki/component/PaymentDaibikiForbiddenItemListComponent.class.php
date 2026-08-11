<?php

class PaymentDaibikiForbiddenItemListComponent extends HTMLList{

	public function populateItem($code, $key, $counter){

		$this->addInput("item_code", array(
			"name"  => "payment_daibiki[item_table][]",
			"value" => $code,
			"attr:tabindex" => $counter + 200
		));

		//商品情報を取得
		$item = soyshop_get_item_object_by_code($code);

		$this->addLink("item_name", array(
			"text"  => $item->getName(),
			"link" => SOY2PageController::createLink("Item.Detail." . $item->getId())
		));
	}
}
