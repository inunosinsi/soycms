<?php

class PaymentDaibikiPriceListComponent extends HTMLList{

	public function populateItem($value, $key, $counter){
		$this->addInput("item_price", array(
			//"name"  => "payment_daibiki[price_table][key][]",
			"value" => strlen($key) ? number_format($key) : "",
			"attr:tabindex" => $counter
		));
		$this->addInput("daibiki_fee", array(
			//"name"  => "payment_daibiki[price_table][price][]",
			"value" => strlen($value) ? number_format($value) : "",
			"attr:tabindex" => $counter + 100
		));

		$this->addLink("delete_button", array(
			"link" => "javascript:void(0);",
			"onclick" => "$(this).parent().parent().remove();",
			"attr:id" => "delete_button_" . $counter
		));
	}
}
