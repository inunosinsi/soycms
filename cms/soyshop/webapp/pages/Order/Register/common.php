<?php

if(!defined("SOYSHOP_CURRENT_CART_ID")){
	define("SOYSHOP_CURRENT_CART_ID","admin_cart");
}
SOY2::import("logic.cart.CartLogic");
SOYShopPlugin::load("soyshop.item.customfield");
MessageManager::addMessagePath("cart");

/**
 * 管理側での注文登録用カート
 */
class AdminCartLogic extends CartLogic{

	//@TODO 注文カスタムフィールドへの対応

	private $orderDate;

	public function log($text){
		error_log("[admin ".$this->getAttribute("page")."] ".$text);
	}

	/**
	 * 注文実行
	 */
	function order(){

		//管理画面からの注文ではモジュールなしも許容する

		//ユーザーエージェント
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$this->setOrderAttribute("order_check_carrier", "ユーザーエージェント", $_SERVER['HTTP_USER_AGENT'], true, true);
		}

		//IPアドレス
		if(isset($_SERVER['REMOTE_ADDR'])){
			$this->setOrderAttribute("order_ip_address", "IPアドレス", $_SERVER['REMOTE_ADDR'], true, true);
		}

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			//transaction start
			$orderDAO->begin();

			//注文可能かチェック
			$this->checkOrderable();

			//顧客情報の登録
			$this->registerCustomerInformation();

			//注文情報の登録
			$this->orderItems();

			//注文カスタムフィールド
			SOYShopPlugin::load("soyshop.order.customfield");
			$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
					"mode" => "order",
					"cart" => $this,
			));

			//ユーザカスタムフィールド
			SOYShopPlugin::load("soyshop.user.customfield");
			SOYShopPlugin::invoke("soyshop.user.customfield",array(
					"mode" => "register",
					"app" => $this,
					"userId" => $this->getCustomerInformation()->getId()
			));

			//記録
			$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

			//登録者
			if(class_exists("UserInfoUtil")){
				$author = UserInfoUtil::getUserName()." (".UserInfoUtil::getUserId().")";
			}else{
				$author = SOY2ActionSession::getUserSession()->getAttribute("username")." (".SOY2ActionSession::getUserSession()->getAttribute("userid").")";
			}
			$orderLogic->addHistory($this->getAttribute("order_id"), $author."が管理画面から注文を登録しました。");
			SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic")->clear();	//バックアップの削除

			$orderDAO->commit();
		}catch(SOYShop_EmptyStockException $e){
			$this->log($e);
			throw $e;
		}catch(SOYShop_OverStockException $e){
			$this->log($e);
			throw $e;
		}catch(Exception $e){
			$this->log("---------- Exception in CartLogic->order() ----------");
			$this->log($e);
			$this->log(var_export($this,true));
			$this->log(var_export($e,true));
			$this->log("---------- /Exception ----------");
			$orderDAO->rollback();
			throw new Exception("注文実行時にエラーが発生しました。");
		}

	}

	/**
	 * 注文日時の指定
	 * @param int $date
	 */
	public function setOrderDate($date){
		if(strlen($date)){
			if(!is_numeric($date)){
				//注文日の指定が本日の場合は時間の登録
				if($date == date("Y-m-d")){
					$date = time();
				}else{
					$date = strtotime($date);
				}
			}
			$this->orderDate = $date;
		}
	}
	public function getOrderDate(){
		return $this->orderDate;
	}
	public function getOrderDateText(){
		if(strlen($this->orderDate)){
			return date("Y-m-d", $this->orderDate);
		}else{
			return "";
		}
	}

	/**
	 * 注文日時の変更
	 * _orderCompleteの後に実行する
	 *
	 */
	public function changeOrderDate(){
		if(!$this->order) return;
		if(!$this->orderDate) return;

		$this->order->setOrderDate($this->orderDate);

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$orderDAO->update($this->order);
		}catch(Exception $e){
			error_log($e);
		}

	}

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
				"cart" => $this,
		));
		return $delegate->getList();
	}

	/**
	 * 有効なポイントモジュールを取得
	 */
	function getPointMethodList($userId){
		SOYShopPlugin::load("soyshop.point.payment");
		$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
			"mode" => "list",
			"cart" => $this,
			"userId" => $this->getCustomerInformation()->getId(),
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
		$this->createAdd("item_option_list", "OptionList", array(
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
			//
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
