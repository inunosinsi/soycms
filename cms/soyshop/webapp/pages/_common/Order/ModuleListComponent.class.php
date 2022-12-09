<?php

class ModuleListComponent extends HTMLList {

	protected function populateItem($entity) {
		$this->addLabel("module_name", array(
			"text" => $entity->getName()
		));

		$this->addLabel("module_price", array(
			"text" => soy2_number_format($entity->getPrice())
		));

		return ($entity->isVisible() && is_numeric($entity->getPrice()));
	}
}
