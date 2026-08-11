<?php

class FeePerPackConfigListComponent extends HTMLList{

	function populateItem($entity, $key, $int){

		foreach(array("pack", "fee") as $idx){
			$this->addInput($idx, array(
				"name" => "Config[fee_per_pack][".$idx."][]",
				"value" => (isset($entity[$idx])) ? (int)$entity[$idx] : 0	
			));
		}
	}
}
