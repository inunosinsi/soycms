<?php
/**
 * @class Discount_methodList
 */
class DiscountMethodListComponent extends HTMLList{
	protected function populateItem($entity, $key, $counter, $length){
		$this->addLabel("discount_name", array(
			"text" => $entity["name"]
		));

		$this->addLabel("discount_description", array(
			"html" => $entity["description"]
		));

		$this->addModel("has_discount_error", array(
			"visible" => (strlen($entity["error"]) > 0)
		));
		$this->addLabel("discount_error", array(
			"text" => $entity["error"]
		));
	}
}
?>