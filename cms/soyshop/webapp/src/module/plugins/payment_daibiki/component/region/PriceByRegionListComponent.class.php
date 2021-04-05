<?php

class PriceByRegionListComponent extends HTMLList{

	private $area;

	public function populateItem($value, $key, $counter){

		$this->addInput("item_price", array(
			"name" => "price_by_region[" . $this->area . "][key][]",
			"value" => soy2_number_format($key),
			"attr:tabindex" => $counter
		));
		$this->addInput("daibiki_fee", array(
			"name" => "price_by_region[" . $this->area . "][price][]",
			"value" => soy2_number_format($value),
			"attr:tabindex" => $counter + 100
		));
	}

	function setArea($area){
		$this->area = $area;
	}
}
