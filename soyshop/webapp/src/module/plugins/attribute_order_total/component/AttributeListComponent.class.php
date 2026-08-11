<?php

class AttributeListComponent extends HTMLList {
	
	protected function populateItem($entity, $key){
		
		$this->addInput("total", array(
			"name" => "total[]",
			"value" => (isset($entity["total"])) ? (int)$entity["total"] : 0,
			"style" => "width:70px;text-align:right;"
		));
		
		$this->addInput("label", array(
			"name" => "label[]",
			"value" => (isset($entity["label"])) ? $entity["label"] : "",
			"style" => "width:70%;"
		));
		
		if($entity["total"] >= 2147483647) return false;
	}
}
?>