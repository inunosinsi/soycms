<?php
class OrderAttributeListComponent extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("attribute_title", array(
			"text" => $entity["name"],
		));

		$this->addLabel("attribute_value", array(
			"text" => $entity["value"],
		));

	}
}
?>