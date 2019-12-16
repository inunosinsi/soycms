<?php

class AttributeListComponent extends HTMLList {
	
	protected function populateItem($entity, $key){
		
		$this->addInput("count", array(
			"name" => "count[]",
			"value" => (isset($entity["count"])) ? (int)$entity["count"] : 0,
			"style" => "width:70px;text-align:right;"
		));
		
		$this->addInput("label", array(
			"name" => "label[]",
			"value" => (isset($entity["label"])) ? $entity["label"] : "",
			"style" => "width:70%;"
		));
		
		if($entity["count"] >= 2147483647) return false;
	}
}
?>