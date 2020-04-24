<?php

class DeliveryTimeConfigListComponent extends HTMLList{

	function populateItem($entity){
		$this->addInput("delivery_time", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}
