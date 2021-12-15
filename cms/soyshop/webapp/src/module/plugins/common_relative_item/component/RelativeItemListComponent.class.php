<?php

class RelativeItemListComponent extends HTMLList{

	protected function populateItem($entity, $key){

		$this->addInput("item_code_input", array(
			"name" => "relative_items[]",
			"value" => (isset($entity) && strlen($entity)) ? $entity : "",
			"id" => "relative_items_" . $key
		));

		$this->addModel("label_for", array(
			"attr:for" => "relative_items_" . $key
		));

		$this->addLabel("item_name", array(
			"text" => (isset($entity) && is_string($entity)) ? self::_getItemName($entity) : ""
		));
	}

	private function _getItemName(string $code){
		$item = soyshop_get_item_object_by_code($code);
		return (is_numeric($item->getId())) ? $item->getName() : "該当の商品が見付かりません";
	}
}
