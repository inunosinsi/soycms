<?php

class ModuleListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addLabel("module_name", array(
			"text" => $entity->getName()
		));

		$this->addLabel("module_price", array(
			"text" => (is_numeric($entity->getPrice())) ? number_format($entity->getPrice()) : 0
		));

		return ($entity->isVisible());
	}
}
