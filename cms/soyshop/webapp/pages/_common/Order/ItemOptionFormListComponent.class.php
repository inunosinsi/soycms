<?php

class ItemOptionFormListComponent extends HTMLList{

	private $itemOrderId;

	protected function populateItem($entity, $key) {

		$label = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "edit",
			"key" => $key
		))->getLabel();

		$this->addLabel("label", array(
			"text" => $label
		));

		$this->addLabel("option", array(
			"html" => self::buildForm($key, $entity)
		));
	}

	private function buildForm($key, $selected){
		$selected = trim(htmlspecialchars($selected, ENT_QUOTES, "UTF-8"));

		$form = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "build",
			"itemOrderId" => $this->itemOrderId,
			"key" => $key,
			"selected" => $selected
		))->getHtmls();

		if(isset($form) && strlen($form)) return $form;

		$name = "Item[" . $this->itemOrderId . "][attributes][" . $key . "]";
		return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\">";
	}

	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}
}
