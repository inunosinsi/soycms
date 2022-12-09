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

		$selected = (is_string($entity)) ? $entity : "";
		$this->addLabel("option", array(
			"html" => (is_string($key)) ? self::_buildForm($key, $selected) : ""
		));
	}

	private function _buildForm(string $key, string $selected=""){
		$selected = trim(htmlspecialchars($selected, ENT_QUOTES, "UTF-8"));

		$form = self::_getItemOptionHtml($key, $selected);
		if(isset($form) && strlen($form)) return $form;

		$name = "Item[" . $this->itemOrderId . "][attributes][" . $key . "]";
		return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\">";
	}

	private function _getItemOptionHtml(string $key, string $selected=""){
		$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "build",
			"itemOrderId" => $this->itemOrderId,
			"key" => $key,
			"selected" => $selected
		))->getHtmls();

		if(!is_array($htmls) || !count($htmls)) return "";

		$html = array();
		foreach($htmls as $h){
			if(!is_string($h) || !strlen($h)) continue;
			$html[] = $h;
		}

		return implode("\n", $html);
	}

	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}
}
