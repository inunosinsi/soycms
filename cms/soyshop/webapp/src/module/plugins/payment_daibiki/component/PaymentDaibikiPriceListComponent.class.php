<?php

class PaymentDaibikiPriceListComponent extends HTMLList{

	public function populateItem($price, $int, $counter){
		$int = (is_numeric($int)) ? $int : null;
		$price = (is_numeric($price)) ? $price : null;

		$this->addInput("item_price", array(
			//"name"  => "payment_daibiki[price_table][key][]",
			"value" => $int,
			"attr:tabindex" => $counter
		));
		$this->addInput("daibiki_fee", array(
			//"name"  => "payment_daibiki[price_table][price][]",
			"value" => $price,
			"attr:tabindex" => $counter + 100
		));

		$this->addLink("delete_button", array(
			"link" => "javascript:void(0);",
			"onclick" => "$(this).parent().parent().remove();",
			"attr:id" => "delete_button_" . $counter
		));

		if(is_null($int) || is_null($price)) return false;
	}
}
