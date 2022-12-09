<?php

class InvoiceModuleListComponent extends HTMLList {

	protected function populateItem($item) {

		$this->addLabel("module_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("module_price", array(
			"text" => soy2_number_format($item->getPrice())
		));

		return $item->isVisible();
	}
}
