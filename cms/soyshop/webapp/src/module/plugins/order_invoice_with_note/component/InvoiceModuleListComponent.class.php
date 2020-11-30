<?php

class InvoiceModuleListComponent extends HTMLList {

	protected function populateItem($item) {

		$this->addLabel("module_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("module_price", array(
			"text" => (is_numeric($item->getPrice())) ? number_format($item->getPrice()) : 0
		));

		return $item->isVisible();
	}
}
