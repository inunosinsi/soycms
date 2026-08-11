<?php

class CommissionListComponent extends HTMLList {

	private $modules;
	private $include = false;	//注文合計の合算に含める項目か？

	protected function populateItem($entity, $key){
		$label = (isset($entity)) ? trim($entity) : null;
		if(isset($label) && $this->include) $label = "(" . $label . ")";
		$this->addLabel("commission_label", array(
			"text" => $label
		));

		$nameProp = (!$this->include) ? "commission_fee[" . $key . "]" : "include[" . $key . "]";
		$postfix = (!$this->include) ? "" : "include_";
		$this->addInput("commission_fee", array(
			"name" => $nameProp,
			"value" => self::getModulePrice($key, $postfix)
		));
	}

	private function getModulePrice($key, $idx){
		if(!is_numeric($key) || !isset($this->modules["payment_commission_" . $idx . $key])) return 0;
		return (int)$this->modules["payment_commission_" . $idx . $key]->getPrice();
	}

	function setModules($modules){
		$this->modules = $modules;
	}

	function setInclude($include){
		$this->include = $include;
	}
}
