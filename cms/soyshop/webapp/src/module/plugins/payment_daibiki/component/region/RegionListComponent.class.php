<?php

class RegionListComponent extends HTMLList {

	private $areas;

	public function populateItem($value, $key, $counter){
		$this->addModel("table_id", array(
			"attr:id" => "price_table_by_region_" . $key
		));

		$this->addLink("add_price_by_region", array(
			"link" => "javascript:void(0);",
			"onclick" => "add_price_by_region(" . $key . ");"
		));

		$this->addLabel("area_name", array(
			"text" => (isset($this->areas[$key])) ? $this->areas[$key] : null
		));

		$this->addLink("delete_price_by_region", array(
			"link" => "javascript:void(0);",
			"onclick" => "delete_price_by_region(this, " . $key . ");"
		));

		$this->addInput("remove_button", array(
			"name" => "by_region_remove[" . $key . "]"
		));

		$this->createAdd("price_by_region_list", "PriceByRegionListComponent", array(
			"list" => $value,
			"area" => $key
		));

		$this->addInput("default_item_price", array(
			"name" => "price_by_region[" . $key . "][key][]",
		));
		$this->addInput("default_daibiki_fee", array(
			"name" => "price_by_region[" . $key . "][price][]",
		));
	}

	function setAreas($areas){
		$this->areas = $areas;
	}
}
