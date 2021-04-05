<?php

class ItemOrderFormListComponent extends HTMLList {

	private $htmlObj;

	protected function populateItem($itemOrder) {

		$id = (is_numeric($itemOrder->getId())) ? (int)$itemOrder->getId() : 0;
		$item = soyshop_get_item_object($itemOrder->getItemId());

		$this->addInput("item_delete", array(
			"name" => "Item[" . $id . "][itemDelete]",
			"value" => 1
		));

		$itemExists = ((int)$itemOrder->getItemId() > 0 && method_exists($item, "getCode") && strlen($item->getCode()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCode() : "Deleted Item (ID=" . $itemOrder->getItemId() . ")",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId()) : "",
		));

		$this->addInput("item_name", array(
			"name" => "Item[" . $id . "][itemName]",
			"value" => $itemOrder->getItemName()
		));

		$this->addInput("item_price", array(
			"name" => "Item[" . $id . "][itemPrice]",
			"value" => $itemOrder->getItemPrice()
		));

		//仕入値
		$this->addModel("is_purchase_price", array(
			"visible" => (self::_isPurchasePrice())
		));

		$this->addLabel("purchase_price", array(
			"text" => soy2_number_format($item->getPurchasePrice())
		));

		$this->addInput("item_count", array(
			"name" => "Item[" . $id . "][itemCount]",
			"value" => $itemOrder->getItemCount()
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($itemOrder->getTotalPrice())
		));


		$orderAttributeList = array();
		if(class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_item_option")){
			$orderAttributeList = (count($itemOrder->getAttributeList()) > 0) ? $itemOrder->getAttributeList() : self::getOptionIndex($itemOrder->getItemId());
		}

		$this->createAdd("item_option_list", "_common.Order.ItemOptionFormListComponent", array(
			"list" => $orderAttributeList,
			"itemOrderId" => $id
		));

	}

	private function getOptionIndex($itemId){
		if(!isset($itemId) || !is_numeric($itemId)) return array();

		$optList = self::attrDao()->getOnLikeSearch($itemId, "item_option_%", true, false);
		if(!count($optList)) return array();

		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return array();

		$array = array();
		foreach($opts as $index => $value){
			if(!isset($optList["item_option_" . $index])) continue;	//商品オプションの設定のないものは除く
			$array[$index] = "";
		}

		return $array;
	}

	private function _isPurchasePrice(){
		static $cnf;
		if(is_null($cnf)) $cnf = SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin();
		return $cnf;
	}

	private function attrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

	function setHtmlObj($obj){
		$this->htmlObj = $obj;
	}
}
