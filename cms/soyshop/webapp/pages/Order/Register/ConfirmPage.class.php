<?php
include(dirname(__FILE__) . "/IndexPage.class.php");
//include(dirname(__FILE__) . "/common.php");

class ConfirmPage extends IndexPage{

	//protected $cart;

	function doPost(){

		if(soy2_check_token()){
			//注文実行

			$cart = $this->cart;

			try{
				//@TODO カスタムフィールド、割引モジュール

				//ポイントモジュール
				{
					SOYShopPlugin::load("soyshop.point.payment");
					$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
						"mode" => "order",
						"cart" => $cart,
					));
				}

				//注文実行
				$cart->order();
				$cart->orderCompleteWithoutMail();

				//注文日時変更
				$cart->changeOrderDate();

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
				$cart->log($e);
				if(DEBUG_MODE){
					$cart->addErrorMessage("order_error","注文の登録に失敗しました。<pre>" . var_export($e,true) . "</pre>");
				}else{
					$cart->addErrorMessage("order_error","注文の登録に失敗しました。");
				}
			}

			$cart->save();
		}

		SOY2PageController::jump("Order.Register");
	}

	function __construct() {
		$this->cart = AdminCartLogic::getCart();
		$this->cart->setAttribute("page", "confirm");

		WebPage::__construct();

		$this->itemInfo();
		$this->dateInfo();
		$this->userInfo();
		$this->addressInfo();
		$this->attributeInfo();
		$this->memoInfo();

		$this->orderForm();
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("確認", array("Order" => "注文管理", "Order.Register" => "注文を追加する"));
	}

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css"
		);
	}

	/**
	 * 注文属性：支払い方法、配送方法、備考など
	 */
	function attributeInfo(){
		include_once(dirname(__FILE__) . "/component/OrderAttributeListComponent.class.php");
		$this->createAdd("order_attribute_list", "OrderAttributeListComponent", array(
			"list" => $this->cart->getOrderAttributes()
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
