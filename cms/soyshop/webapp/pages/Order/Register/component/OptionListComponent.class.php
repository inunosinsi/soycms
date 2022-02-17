<?php

class OptionListComponent extends HTMLList{

	private $index;
	private $itemId;
	private $configs;
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

		$form = (is_string($key) && is_string($entity)) ? self::buildForm($key, $entity) : "";
		$this->addModel("is_option", array(
			"visible" => (strlen($form))
		));

		$this->addLabel("option_form", array(
			"html" => $form
		));
	}

	private function buildForm(string $key, string $selected){
		if(!isset($key) || !isset($this->configs[$key])) return "";
		switch($this->configs[$key]){
			case "text":
				return self::_buildForm($key, $selected);
			default:
				return (strlen(ItemOptionUtil::getFieldValue($key, $this->itemId))) ? self::_buildForm($key, $selected) : "";
		}
	}

	private function _buildForm(string $key, string $selected){
		$selected = trim(htmlspecialchars($selected, ENT_QUOTES, "UTF-8"));

		$form = self::getItemOptionHtml($key, $selected);
		if(isset($form) && strlen($form)) return $form;

		$name = "Item[" . $this->index . "][attributes][" . $key . "]";
		return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\">";
	}

	private function getItemOptionHtml(string $key, string $selected){
		$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "admin",
			"index" => $this->index,
			"fieldValue" => (isset($this->attrs["item_option_" . $key])) ? $this->attrs["item_option_" . $key] : "",
			"key" => $key,
			"selected" => $selected
		))->getHtmls();

		if(!is_array($htmls) || !count($htmls)) return "";

		$html = array();
		foreach($htmls as $h){
			if(!strlen($h)) continue;
			$html[] = $h;
		}

		return implode("\n", $html);
	}

	function setIndex($index){
		$this->index = $index;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	function setConfigs($configs){
		$this->configs = $configs;
	}
	function setAttrs($attrs){
		$this->attrs = $attrs;
	}
}
