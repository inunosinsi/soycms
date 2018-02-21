<?php

include_once(dirname(__FILE__) . "/OptionListComponent.class.php");
class ItemListComponent extends HTMLList {

	private $cart;

	protected function populateItem($entity, $id) {

		$itemId = (int)$entity->getItemId();
		$item = self::getItem($itemId);

		$this->addInput("item_delete", array(
			"name" => "Item[$id][itemDelete]",
			"value" => 1
		));

		$itemExists = (method_exists($item, "getCode") && strlen($item->getCode()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCode() : "",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $entity->getItemId()) : "",
		));
		$this->addLabel("item_id_text", array(
			"text" => $itemExists ? $item->getCode() : "",
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
			"text" => $entity->getItemName(),
		));

		$this->addLink("change_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "open_window_with_change(" . $id . ")",
			"attr:id" => "change_item_" . $id
		));

		$this->addInput("item_price", array(
			"name" => "Item[$id][itemPrice]",
			"value" => $entity->getItemPrice(),
		));
		$this->addLabel("item_price_text", array(
			"text" => number_format($entity->getItemPrice()),
		));

		$this->addInput("item_count", array(
			"name" => "Item[$id][itemCount]",
			"value" => $entity->getItemCount(),
		));
		$this->addLabel("item_count_text", array(
			"text" => number_format($entity->getItemCount()),
		));

		$this->addLabel("item_total_price", array(
			"text" => number_format($entity->getTotalPrice())
		));

		$opts = (get_class($entity) == "SOYShop_ItemOrder") ? self::getOptionList($entity) : array();
		$this->createAdd("item_option_list", "OptionListComponent", array(
			"list" => $opts,
			"attrs" => self::getItemOptionAttributeById($itemId),
			"orderId" => $id,
		));

		$this->addLabel("item_option", array(
			"html" => (count($opts)) ? self::buildOptionList($opts) : null
		));

		//在庫切れかどうか？
		$this->addModel("out_of_stock", array(
			"visible" => ($entity->getItemCount() > $item->getStock())
		));
	}

	private function getOptionList(SOYShop_ItemOrder $itemOrder){
		if(!SOYShopPluginUtil::checkIsActive("common_item_option")) return array();
		if(count($itemOrder->getAttributeList()) > 0) return $itemOrder->getAttributeList();

		$list = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic")->getOptions();
		if(!count($list)) return array();

		//商品に紐付いている設定を取得
		//$attrs = self::getItemOptionAttributeById($itemOrder->getItemId());

		$array = array();
		foreach($list as $idx => $v){
			//if(!isset($attrs["item_option_" . $idx])) continue;
			//$array[$idx] = $attrs["item_option_" . $idx];
			$array[$idx] = "";
		}

		return $array;
	}

	private function buildOptionList($opts){
		$list = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic")->getOptions();
		if(!count($list)) return null;

		$html = array();
		foreach($opts as $optionId => $opt){
			if(!isset($list[$optionId])) continue;
			$html[] = $list[$optionId]["name"] . ":" . $opt;
		}

		return implode("<br>", $html);
	}

	public function setCart($cart){
		$this->cart = $cart;
	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	private function getItem($itemId){
		static $itemDAO;
		static $items = array();

		if(!$itemDAO)$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!isset($items[$itemId])){
			try{
				$items[$itemId] = $itemDAO->getById($itemId);
			}catch(Exception $e){
				$items[$itemId] = new SOYShop_Item();
			}
		}
		return $items[$itemId];
	}

	private function getItemOptionAttributeById($itemId){
		static $dao;
		static $list;

		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(is_null($list)) $list = array();

		if(isset($list[$itemId])) return $list[$itemId];

		//今見ている商品が子商品であるか調べる
		$type = self::getItem($itemId)->getType();
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
}
