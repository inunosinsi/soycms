<?php

class DeliveryTimeConfigListComponent extends HTMLList{

	function populateItem($entity, $key, $int){
		$this->addInput("delivery_time", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}
