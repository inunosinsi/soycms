<?php

class SpecialPriceExportListComponent extends HTMLList {

	protected function populateItem($entity){
		$this->addCheckBox("special_price_input", array(
			"label" => (isset($entity["label"])) ? $entity["label"] . "価格" : null,
			"name" => (isset($entity["hash"])) ? "item[customfield(np_" . $entity["hash"] . ")]" : null,
			"value" => 1,
			"selected" => true
		));

		$this->addCheckBox("special_price_sale_input", array(
			"label" => (isset($entity["label"])) ? $entity["label"] . "価格(セール価格)" : null,
			"name" => (isset($entity["hash"])) ? "item[customfield(np_" . $entity["hash"] . "_sale)]" : null,
			"value" => 1,
			"selected" => true
		));
	}
}
