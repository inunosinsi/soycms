<?php

class DiscountPlanListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addInput("stock", array(
			"name" => "Config[][stock]",
			"value" => (isset($entity["stock"]) && is_numeric($entity["stock"])) ? (int)$entity["stock"] : "",
			"style" => "width:40%;ime-mode:inactive;"
		));
		
		$this->addInput("discount", array(
			"name" => "Config[][discount]",
			"value" => (isset($entity["discount"]) && is_numeric($entity["discount"])) ? (int)$entity["discount"] : "",
			"style" => "width:50%;ime-mode:inactive;"
		));
	}
}
?>