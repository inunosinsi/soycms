<?php

include_once(dirname(__FILE__) . "/OptionListComponent.class.php");
class ItemListComponent extends HTMLList {

	private $cart;

	protected function populateItem($entity, $id) {

		$itemId = (int)$entity->getItemId();
		$item = soyshop_get_item_object($itemId);

		$this->addInput("item_delete", array(
			"name" => "Item[$id][itemDelete]",
			"value" => 1
		));

		$itemExists = (method_exists($item, "getCodeOnAdmin") && $itemId > 0 && strlen($item->getCodeOnAdmin()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCodeOnAdmin() : "",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $entity->getItemId()) : "",
		));
		$this->addLabel("item_id_text", array(
			"text" => $itemExists ? $item->getCodeOnAdmin() : "",
		));
		$this->addInput("item_id_hidden", array(
			"name" => "Item[$id][itemId]",
			"value" => $entity->getItemId(),
		));

		$this->addInput("item_name", array(
			"name" => "Item[$id][itemName]",
			"value" => $entity->getItemName(),
		));
		$this->addLabel("item_name_text", array(
			"text" => $entity->getItemNameOnAdmin(),
		));

		$this->addLink("change_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "open_window_with_change(" . $id . ")",
			"attr:id" => "change_item_" . $id
		));

		$priceList = self::itemLogic()->getItemPriceListByItemId($item->getId());

		$this->addModel("display_price_list_link", array(
			"visible" => (count($priceList) > 2)	//セール価格があったり会員特別価格があったり
		));

		$this->addLink("price_list_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "open_window_item_price(" . $item->getId() . ")",
		));

		$this->addInput("item_price", array(
			"name" => "Item[$id][itemPrice]",
			"value" => $entity->getItemPrice(),
		));
		$this->addLabel("item_price_text", array(
			"text" => soy2_number_format($entity->getItemPrice()),
		));

		//仕入値
		$this->addModel("is_purchase_price", array(
			"visible" => self::_isPurchasePrice()
		));

		$this->addLabel("purchase_price", array(
			"text" => soy2_number_format($item->getPurchasePrice())
		));

		$this->addInput("item_count", array(
			"name" => "Item[$id][itemCount]",
			"value" => $entity->getItemCount(),
		));
		$this->addLabel("item_count_text", array(
			"text" => soy2_number_format($entity->getItemCount()),
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($entity->getTotalPrice())
		));

		$opts = (get_class($entity) == "SOYShop_ItemOrder") ? self::getOptionList($entity) : array();
		$this->createAdd("item_option_list", "OptionListComponent", array(
			"list" => $opts,
			"index" => $id,
			"itemId" => $itemId,
			"configs" => (count($opts)) ? self::getOptionConfig() : array(),
			"attrs" => (count($opts)) ? self::getItemOptionAttributeById($itemId) : array(),
		));

		$this->addModel("option_pluign_active", array(
			"visible" => (SOYShopPluginUtil::checkIsActive("common_item_option") && (int)$entity->getItemId() > 0)
		));

		//商品一覧へのリンク
		$this->addLink("item_edit_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $entity->getItemId()) . "?registration_in"
		));

		$this->addLabel("item_option", array(
			"html" => (count($opts)) ? self::buildOptionList($opts) : null
		));

		//在庫切れかどうか？
		$this->addModel("out_of_stock", array(
			"visible" => (!self::checkIgnoreStockMode() && $entity->getItemCount() > $item->getStock())
		));
	}

	private function getOptionList(SOYShop_ItemOrder $itemOrder){
		if(!SOYShopPluginUtil::checkIsActive("common_item_option")) return array();
		if(count($itemOrder->getAttributeList()) > 0) return $itemOrder->getAttributeList();

		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
		$configs = self::getOptionConfig();
		if(!count($configs)) return array();

		//商品に紐付いている設定を取得
		//$attrs = self::getItemOptionAttributeById($itemOrder->getItemId());

		$array = array();
		foreach($configs as $key => $conf){
			$array[$key] = "";
		}

		return $array;
	}

	private function getOptionConfig(){
		static $configs;
		if(is_null($configs)) $configs = ItemOptionUtil::getOptions();
		return $configs;
	}

	private function buildOptionList($opts){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
		$list = ItemOptionUtil::getOptions();
		if(!count($list)) return null;

		$html = array();
		foreach($opts as $optionId => $opt){
			if(!isset($list[$optionId]) || !strlen($opt)) continue;
			$html[] = $list[$optionId]["name"] . ":" . $opt;
		}

		return implode("<br>", $html);
	}

	private function getItemOptionAttributeById($itemId){
		static $dao, $list;
		if(is_null($dao)){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$list = array();
		}

		if(isset($list[$itemId])) return $list[$itemId];

		//今見ている商品が子商品であるか調べる
		$type = soyshop_get_item_object($itemId)->getType();
		if(is_numeric($type)) $itemId = $type;

		$list[$itemId] = array();
		try{
			$attrs = $dao->getByItemId($itemId);
		}catch(Exception $e){
			$attrs = array();
		}

		if(count($attrs)){
			$values = array();
			foreach($attrs as $key => $attr){
				if(strpos($key, "item_option_") !== 0 || !strlen($attr->getValue())) continue;
				$values[$key] = $attr->getValue();
			}
			$list[$itemId] = $values;
		}

		return $list[$itemId];
	}

	private function checkIgnoreStockMode(){
		static $on;
		if(is_null($on)) $on = ((int)SOYShop_ShopConfig::load()->getIgnoreStock() == 1);
		return $on;
	}

	private function _isPurchasePrice(){
		static $cnf;
		if(is_null($cnf)) $cnf = SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin();
		return $cnf;
	}

	private function itemLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");
		return $logic;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
