<?php

class ModuleFormListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addInput("module_delete", array(
			"name" => "Module[" . $entity->getId() . "][delete]",
			"value" => 1
		));

		$this->addInput("module_name", array(
			"name" => "Module[" . $entity->getId() . "][name]",
			"value" => $entity->getName()
		));

		$this->addCheckBox("module_is_include", array(
			"name" => "Module[" . $entity->getId() . "][isInclude]",
			"value" => 1,
			"selected" => $entity->getIsInclude(),
			"label" => "代金合計に含める"
		));

		$this->addInput("module_price", array(
			"name" => "Module[" . $entity->getId() . "][price]",
			"value" => $entity->getPrice()
		));

		return ($entity->isVisible());
	}
}
