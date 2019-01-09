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

		$form = self::getItemOptionHtml($key, $selected);
		if(isset($form) && strlen($form)) return $form;

		$name = "Item[" . $this->itemOrderId . "][attributes][" . $key . "]";
		return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\">";
	}

	private function getItemOptionHtml($key, $selected){
		if(is_null($key)) return "";

		$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "build",
			"itemOrderId" => $this->itemOrderId,
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

	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}
}
