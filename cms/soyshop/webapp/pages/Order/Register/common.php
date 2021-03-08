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

			//注文カスタムフィールド　顧客情報の変更を必要とするものもあるため、先に実行しておく
			SOYShopPlugin::load("soyshop.order.customfield");
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "order",
				"cart" => $this,
			));

			//顧客情報の登録
			$this->registerCustomerInformation();

			//ユーザカスタムフィールド
			SOYShopPlugin::load("soyshop.user.customfield");
			SOYShopPlugin::invoke("soyshop.user.customfield",array(
					"mode" => "register",
					"app" => $this,
					"userId" => $this->getCustomerInformation()->getId()
			));

			//注文情報の登録
			$this->orderItems();

			//もう一度カスタムフィールドを実行する
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "complete",
				"cart" => $this,
			));

			//記録
			$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

			//登録者
			$loginId = (class_exists("UserInfoUtil")) ? UserInfoUtil::getUserId() : null;
			if(isset($loginId)){
				$author = UserInfoUtil::getUserName()." (".$loginId.")";
			}else{
				$author = SOY2ActionSession::getUserSession()->getAttribute("username")." (".SOY2ActionSession::getUserSession()->getAttribute("userid").")";
			}
			$orderLogic->addHistory($this->getAttribute("order_id"), $author."が管理画面から注文を登録しました。");
			SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic")->clear();	//バックアップの削除

			$orderDAO->commit();

			//CartLogicの内容の一部をSQLite DBに移行するモードの場合はデータベースを削除する
			if(defined("SOYSHOP_USE_CART_TABLE_MODE") && SOYSHOP_USE_CART_TABLE_MODE){
				soyshop_cart_delete_db($this->db);
				soyshop_cart_routine_delete_db();
			}
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

		//CartLogicの内容の一部をSQLite DBに移行するモードの場合はデータベースを削除する
		if(defined("SOYSHOP_USE_CART_TABLE_MODE") && SOYSHOP_USE_CART_TABLE_MODE){
			$items = soyshop_cart_get_items($this->db);
			$items[] = $obj;
			$this->db = soyshop_cart_set_items($this->db, $items);
			$this->save();
		}else{
			$this->items[] = $obj;
		}
	}
}
