<?php

class ItemOrderFormListComponent extends HTMLList {

	private $htmlObj;

	protected function populateItem($itemOrder) {

		$id = $itemOrder->getId();
		$item = $this->htmlObj->getItem($itemOrder->getItemId());

		$this->addInput("item_delete", array(
			"name" => "Item[$id][itemDelete]",
			"value" => 1
		));

		$itemExists = (method_exists($item, "getCode") && strlen($item->getCode()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCode() : "Deleted Item (ID=" . $itemOrder->getItemId() . ")",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId()) : "",
		));

		$this->addInput("item_name", array(
			"name" => "Item[$id][itemName]",
			"value" => $itemOrder->getItemName()
		));

		$this->addInput("item_price", array(
			"name" => "Item[$id][itemPrice]",
			"value" => $itemOrder->getItemPrice()
		));

		$this->addInput("item_count", array(
			"name" => "Item[$id][itemCount]",
			"value" => $itemOrder->getItemCount()
		));

		$this->addLabel("item_total_price", array(
			"text" => number_format($itemOrder->getTotalPrice())
		));


		$orderAttributeList = array();
		if(class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_item_option")){
			$orderAttributeList = (count($itemOrder->getAttributeList()) > 0) ? $itemOrder->getAttributeList() : $this->getOptionIndex();
		}

		$this->createAdd("item_option_list", "_common.Order.ItemOptionFormListComponent", array(
			"list" => $orderAttributeList,
			"orderId" => $id
		));

	}

	function getOptionIndex(){
		$logic = new ItemOptionLogic();
		$list = $logic->getOptions();

		$array = array();
		foreach($list as $index => $value){
			$array[$index] = "";
		}

		return $array;
	}

	function setHtmlObj($obj){
		$this->htmlObj = $obj;
	}

}
?>