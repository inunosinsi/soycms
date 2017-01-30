<?php

class OrderItemListComponent extends HTMLList{
	
	private $orderConfig;
	
	protected function populateItem($entity,$key){
		
		$this->addLabel("item_name", array(
			"text" => $entity
		));
		
		$this->addCheckBox("display_item", array(
			"selected" => (isset($this->orderConfig[$key]) && $this->orderConfig[$key] === true),
			"value" => 1,
			"name" => "Config[orderItemConfig][" . $key . "]"
		));
	}
	
	function setOrderConfig($orderConfig){
		$this->orderConfig = $orderConfig;
	}
}
?>