<?php

class ItemOptionFormListComponent extends HTMLList{

	private $orderId;

	protected function populateItem($entity, $key) {

		$id = $this->orderId;

		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "edit",
			"key" => $key
		));

		$this->addLabel("label", array(
			"text" => $delegate->getLabel()
		));

		$this->addInput("option", array(
			"name" => "Item[" . $id . "][attributes][" . $key . "]",
			"value" => $entity
		));
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
?>