<?php

class OptionListComponent extends HTMLList{

	private $orderId;
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

		// $this->addInput("option", array(
		// 	"name" => "Item[" . $id."][attributes][" . $key."]",
		// 	"value" => $entity
		// ));

		//セレクトボックスに変更
		$opts = (isset($key) && strlen($key)) ? self::buildOptions($key) : array();
		$this->addModel("is_option", array(
			"visible" => count($opts)
		));
		$this->addSelect("option", array(
			"name" => "Item[" . $this->orderId . "][attributes][" . $key . "]",
			"options" => $opts,
			"selected" => $entity
		));
	}

	private function buildOptions($idx){
		if(!isset($this->attrs["item_option_" . $idx])) return array();
		$opts = explode("\n", $this->attrs["item_option_" . $idx]);
		for($i = 0; $i < count($opts); $i++){
			$v = trim($opts[$i]);
			if(!strlen($v)) continue;
			$opts[$i] = $v;
		}
		return $opts;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
	function setAttrs($attrs){
		$this->attrs = $attrs;
	}
}
