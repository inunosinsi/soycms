<?php
include(dirname(__FILE__) . "/IndexPage.class.php");
//include(dirname(__FILE__) . "/common.php");

class ConfirmPage extends IndexPage{

	//protected $cart;

	function doPost(){
		//あえてsoy2_check_tokenなし

		if(soy2_check_token()){
			//注文実行

			$cart = $this->cart;

			try{
				//@TODO カスタムフィールド、ポイントモジュール、割引モジュール

				//注文実行
				$cart->order();
				$cart->orderCompleteWithoutMail();

				$orderId = $cart->getAttribute("order_id");

				//カートをクリア
				$cart->clear();

				//注文詳細画面へ移動
				SOY2PageController::jump("Order.Detail" . "." . $orderId);

			}catch(SOYShop_EmptyStockException $e){
				$cart->addErrorMessage("stock", "在庫切れの商品があります。");
			}catch(SOYShop_OverStockException $e){
				$cart->addErrorMessage("stock", "在庫切れの商品があります。");
			}catch(Exception $e){
				if(DEBUG_MODE){
					$cart->addErrorMessage("order_error","注文の登録に失敗しました。<pre>" . var_export($e,true) . "</pre>");
				}else{
					$cart->addErrorMessage("order_error","注文の登録に失敗しました。");
				}
			}

			$cart->save();
		}

		SOY2PageController::jump("Order.Register.Confirm");
	}

	function __construct() {
		$this->cart = AdminCartLogic::getCart();

		parent::__construct();
		
		//在庫切れのエラー
		DisplayPlugin::toggle("stock_error", !is_null($this->cart->getErrorMessage("stock")));
		
		$this->itemInfo();
		$this->moduleInfo();
		$this->userInfo();
		$this->addressInfo();
		$this->attributeInfo();
		$this->memoInfo();
		
		$this->orderForm();
	}

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css"
		);
	}

	/**
	 * モジュール料金
	 */
	function moduleInfo(){
		$modules = $this->cart->getModules();
		$this->createAdd("module_list", "ModuleList", array(
			"list" => $modules
		));
	}

	/**
	 * 注文属性：支払い方法、配送方法、備考など
	 */
	function attributeInfo(){
		$attr = $this->cart->getOrderAttributes();
		$this->createAdd("order_attribute_list", "OrderAttributeList", array(
			"list" => $attr
		));
	}

	/**
	 * 注文実行フォーム
	 */
	function orderForm(){

		$items = $this->cart->getItems();
		$user = $this->cart->getCustomerInformation();

		$enabled = count($items) && strlen($user->getMailAddress());

		$this->addForm("order_form", array(
			"disabled" => !$enabled,
		));

		$this->addInput("order_button", array(
			"type" => "submit",
			"name" => "order",
			"value" => "以上の内容で注文実行",
			"disabled" => !$enabled,
		));
	}
}
