<?php

if(!defined("SOYSHOP_CURRENT_CART_ID")){
	define("SOYSHOP_CURRENT_CART_ID","admin_cart");
}
SOY2::import("logic.cart.CartLogic");

/**
 * 管理側での注文登録用カート
 */
class AdminCartLogic extends CartLogic{

	/**
	 * カートを取得
	 */
	public static function getCart($cartId = null){

		if(!$cartId) $cartId = SOYSHOP_CURRENT_CART_ID;
		$userSession = SOY2ActionSession::getUserSession();
		$cart = soy2_unserialize($userSession->getAttribute("soyshop_" . SOYSHOP_ID . $cartId));

		return ($cart instanceof AdminCartLogic) ? $cart : new AdminCartLogic($cartId);
	}

	/**
	 * 有効な支払いモジュールを取得
	 */
	function getPaymentMethodList(){
    	SOYShopPlugin::load("soyshop.payment");
		$delegate = SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "list",
			"cart" => $this
		));
		return $delegate->getList();
	}

	/**
	 * 有効な配送モジュールを取得
	 */
	function getDeliveryMethodList(){
    	SOYShopPlugin::load("soyshop.delivery");
		$delegate = SOYShopPlugin::invoke("soyshop.delivery", array(
			"mode" => "list",
			"cart" => $this
		));
		return $delegate->getList();
	}

	/**
	 * 選択された支払いモジュールを取得
	 */
	function getPaymentMethod(){
		$selected = $this->getAttribute("payment_module");
		if($selected){
			$list = $this->getPaymentMethodList();
			foreach($list as $id => $module){
				if($selected == $id){
					return $module;
				}
			}
		}
		return false;
	}

	/**
	 * 選択された配送モジュールを取得
	 */
	function getDeliveryMethod(){
		$selected = $this->getAttribute("delivery_module");
		if($selected){
			$list = $this->getDeliveryMethodList();
			foreach($list as $id => $module){
				if($selected == $id){
					return $module;
				}
			}
		}
		return false;
	}

	/**
	 * 登録されていない商品を追加
	 */
	function addUnlistedItem($name, $count, $price){
		$obj = new SOYShop_ItemOrder();
		$obj->setItemId(0);//存在しない商品はID=0
		$obj->setItemCount($count);
		$obj->setItemPrice($price);
		$obj->setTotalPrice($price * $count);
		$obj->setItemName($name);

		$this->items[] = $obj;
	}
}


class ItemList extends HTMLList {

	protected function populateItem($entity, $id) {

		$item = $this->getItem($entity->getItemId());

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

		$this->addInput("item_name", array(
			"name" => "Item[$id][itemName]",
			"value" => $entity->getItemName(),
		));
		$this->addLabel("item_name_text", array(
			"text" => $entity->getItemName(),
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

/*
		$orderAttributeList = array();
		if(class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_item_option")){
			$orderAttributeList = (count($entity->getAttributeList()) > 0) ? $itemOrder->getAttributeList() : $this->getOptionIndex();
		}
*/

		$this->createAdd("item_option_list", "OptionList", array(
			"list" => array(),//$orderAttributeList,
			"orderId" => $id,
		));
	}
/*
	function getOptionIndex(){
		$logic = new ItemOptionLogic();
		$list = $logic->getOptions();

		$array = array();
		foreach($list as $index => $value){
			$array[$index] = "";
		}

		return $array;
	}
*/
	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	function getItem($itemId){
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
}

class ModuleList extends HTMLList {
	protected function populateItem($item) {
		$this->addLabel("module_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("module_price", array(
			"text" => number_format($item->getPrice())
		));

		return $item->isVisible();
	}
}

class OrderAttributeList extends HTMLList{
	protected function populateItem($entity){
		$this->addLabel("attribute_title", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : "",
		));
		$this->addLabel("attribute_value", array(
			"text" => (isset($entity["value"])) ? $entity["value"] : "",
		));
	}
}

class OptionList extends HTMLList{

	private $orderId;

	function OptionList(){
		SOYShopPlugin::load("soyshop.item.option");
	}

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
			"name" => "Item[" . $id."][attributes][" . $key."]",
			"value" => $entity
		));
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
