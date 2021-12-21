<?php

class SpecialPriceExportListComponent extends HTMLList {

	protected function populateItem($entity){
		$this->addCheckBox("special_price_input", array(
			"label" => (isset($entity["label"])) ? $entity["label"] . "価格" : "",
			"name" => (isset($entity["hash"])) ? "item[customfield(np_" . $entity["hash"] . ")]" : "",
			"value" => 1,
			"selected" => true
		));

		$this->addCheckBox("special_price_sale_input", array(
			"label" => (isset($entity["label"])) ? $entity["label"] . "価格(セール価格)" : "",
			"name" => (isset($entity["hash"])) ? "item[customfield(np_" . $entity["hash"] . "_sale)]" : "",
			"value" => 1,
			"selected" => true
		));
	}
}
