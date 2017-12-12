<?php

class CommissionListComponent extends HTMLList {

	private $modules;

	protected function populateItem($entity, $key){
		$this->addLabel("commission_label", array(
			"text" => (isset($entity)) ? $entity : ""
		));

		$this->addInput("commission_fee", array(
			"name" => "commission_fee[" . $key . "]",
			"value" => self::getModulePrice($key)
		));
	}

	private function getModulePrice($key){
		if(!is_numeric($key) || !isset($this->modules["payment_commission_" . $key])) return 0;
		return (int)$this->modules["payment_commission_" . $key]->getPrice();
	}

	function setModules($modules){
		$this->modules = $modules;
	}
}
