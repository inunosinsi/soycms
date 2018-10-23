<?php

class OptionListComponent extends HTMLList{

	private $index;
	private $attrs;

	function __construct(){
		SOYShopPlugin::load("soyshop.item.option");
	}

	protected function populateItem($entity, $key) {
		$label = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "edit",
			"key" => $key
		))->getLabel();

		$this->addLabel("label", array(
			"text" => $label
		));

		$this->addLabel("option_form", array(
			"html" => self::buildForm($key, $entity)
		));
	}

	private function buildForm($key, $selected){
		$selected = trim(htmlspecialchars($selected, ENT_QUOTES, "UTF-8"));

		$form = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "admin",
			"index" => $this->index,
			"fieldValue" => (isset($this->attrs["item_option_" . $key])) ? $this->attrs["item_option_" . $key] : "",
			"key" => $key,
			"selected" => $selected
		))->getHtmls();

		if(isset($form) && strlen($form)) return $form;

		$name = "Item[" . $this->index . "][attributes][" . $key . "]";
		return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\">";
	}

	function setIndex($index){
		$this->index = $index;
	}
	function setAttrs($attrs){
		$this->attrs = $attrs;
	}
}
