<?php

class ModuleFormListComponent extends HTMLList {

	protected function populateItem($item) {

		$this->addInput("module_delete", array(
			"name" => "Module[" . $item->getId() . "][delete]",
			"value" => 1
		));

		$this->addInput("module_name", array(
			"name" => "Module[" . $item->getId() . "][name]",
			"value" => $item->getName()
		));

		$this->addInput("module_price", array(
			"name" => "Module[" . $item->getId() . "][price]",
			"value" => $item->getPrice()
		));

		return $item->isVisible();
	}
}
?>