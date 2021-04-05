<?php
/**
 * @class Cart03Page
 * @date 2009-07-16T17:00:20+09:00
 * @author SOY2HTMLFactory
 */
class Cart03Page extends MobileCartPageBase{

	function doPost(){

		if(isset($_POST["next"]) || isset($_POST["next_x"])){

			$cart = CartLogic::getCart();

			//まずはエラーチェックのみ
			$this->checkError($cart);

			$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

			//古いのをクリア
			$cart->removeModule($cart->getAttribute("payment_module"));
			$cart->removeModule($cart->getAttribute("delivery_module"));
			$cart->clearAttribute("payment_module");
			$cart->clearAttribute("delivery_module");

			SOYShopPlugin::load("soyshop.order.customfield");
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "clear",
				"cart" => $cart,
			));

			//支払
			if(!$cart->hasError("payment")){
				$paymentModule = @$_POST["payment_module"];
				if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
					$paymentModule = mb_convert_encoding($paymentModule,"UTF-8","SJIS");
				}
				$cart->setAttribute("payment_module",$paymentModule);

				$paymentModule = $moduleDAO->getByPluginId($paymentModule);
				SOYShopPlugin::load("soyshop.payment",$paymentModule);

				SOYShopPlugin::invoke("soyshop.payment", array(
					"mode" => "select",
					"cart" => $cart
				));
			}

			//配送
			if(!$cart->hasError("delivery")){
				$deliveryMethod = @$_POST["delivery_module"];
				if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
					$deliveryMethod = mb_convert_encoding($deliveryMethod,"UTF-8","SJIS");
				}
				$cart->setAttribute("delivery_module",$deliveryMethod);

				$deliveryModule = $moduleDAO->getByPluginId($deliveryMethod);

				SOYShopPlugin::load("soyshop.delivery",$deliveryModule);

				SOYShopPlugin::invoke("soyshop.delivery", array(
					"mode" => "select",
					"cart" => $cart
				));
			}

			//割引
			if(!$cart->hasError("discount")){
				SOYShopPlugin::load("soyshop.discount");
				SOYShopPlugin::invoke("soyshop.discount", array(
					"mode" => "select",
					"cart" => $cart,
					"param" => @$_POST["discount_module"]
				));
			}

			//カスタムフィールド
			if(!$cart->hasError("customfield")){
//				SOYShopPlugin::load("soyshop.order.customfield");
				SOYShopPlugin::invoke("soyshop.order.customfield", array(
					"mode" => "post",
					"cart" => $cart,
					"param" => @$_POST["customfield_module"]
				));
			}

			//エラーがなければ次へ
			if($cart->hasError()){
				$cart->setAttribute("page", "Cart03");
			}else{
				$cart->setAttribute("page", "Cart04");
			}

			$cart->save();

			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
			}
			soyshop_redirect_cart($param);
		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart02");

			//戻るときにモジュールを削除しない：入力内容を保持しておく
//			$cart->clearAttribute("payment_module");
//			$cart->clearAttribute("delivery_module");

			$cart->clearErrorMessage();

			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
			}
			soyshop_redirect_cart($param);

		}

	}

	function Cart03Page(){
		SOYShopPlugin::active("soyshop.payment");
		SOYShopPlugin::active("soyshop.delivery");
		SOYShopPlugin::active("soyshop.discount");
		SOYShopPlugin::active("soyshop.order.customfield");

		parent::__construct();

		$url = soyshop_get_cart_url(false);
		if(isset($_GET[session_name()])){
				$url = $url."?".session_name() . "=" . session_id();
		}


		$this->createAdd("order_form","HTMLForm", array(
			"action" => $url,
		));

		//商品リストの出力
		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$this->createAdd("item_list", "_common.ItemList", array(
			"list" => $items
		));

		$this->buildForm($cart);

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		//ユーザ情報の出力
		$this->outputUser($cart);

		//エラー周り
		if(!$cart->hasError()){
			DisplayPlugin::hide("has_error");
		}
		$this->appendErrors($cart);
	}

	function buildForm($cart){
		$this->createAdd("payment_method_list", "Payment_methodList", array(
			"list" => $this->getPaymentMethod($cart),
			"selected" => $cart->getAttribute("payment_module")
		));

		$this->createAdd("delivery_method_list","Delivery_methodList", array(
			"list" => $this->getDeliveryMethod($cart),
			"selected" => $cart->getAttribute("delivery_module")
		));

		$discountModuleList = $this->getDiscountMethod($cart);
		$this->createAdd("has_discount_method","HTMLModel", array(
			"visible" => count($discountModuleList) >0,
		));
		$this->createAdd("discount_method_list","Discount_methodList", array(
			"list" => $discountModuleList,
		));

		$customfieldModuleList = $this->getCustomfieldMethod($cart);
		$this->createAdd("has_customfield_method","HTMLModel", array(
			"visible" => count($customfieldModuleList) >0,
		));
		$this->createAdd("customfield_method_list","Customfield_methodList", array(
			"list" => $customfieldModuleList,
		));

		$this->createAdd("myMessage","HTMLLabel", array(
			"text" => "",
		));
	}

	function outputUser($cart){

		$user = $cart->getCustomerInformation();

		$this->createAdd("user_name","HTMLLabel", array(
			"text" => $user->getName()
		));

		$this->createAdd("user_reading","HTMLLabel", array(
			"text" => $user->getReading()
		));

		$send = $cart->getAddress();

		$this->createAdd("send_name","HTMLLabel", array(
			"text" => $send["name"]
		));

		$this->createAdd("send_reading","HTMLLabel", array(
			"text" => $send["reading"]
		));

		$this->createAdd("send_zip_code","HTMLLabel", array(
			"text" => $send["zipCode"]
		));

		$this->createAdd("send_area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($send["area"])
		));

		$this->createAdd("send_address1","HTMLLabel", array(
			"text" => $send["address1"]
		));

		$this->createAdd("send_address2","HTMLLabel", array(
			"text" => $send["address2"]
		));

		$this->createAdd("send_tel","HTMLLabel", array(
			"text" => $send["telephoneNumber"]
		));

		$this->createAdd("is_use_address","HTMLModel", array(
			"visible" => false == (
					empty($send["name"]) &&
					empty($send["reading"]) &&
					empty($send["zipCode"]) &&
					empty($send["area"]) &&
					empty($send["address1"]) &&
					empty($send["address2"]) &&
					empty($send["telephoneNumber"])
			)
		));
	}

	function getPaymentMethod($cart){

    	SOYShopPlugin::load("soyshop.payment");

		$delegate = SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();
	}

	function getDeliveryMethod($cart){

		SOYShopPlugin::load("soyshop.delivery");

		$delegate = SOYShopPlugin::invoke("soyshop.delivery", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();

	}

	function getDiscountMethod($cart){

		SOYShopPlugin::load("soyshop.discount");

		$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();

	}

	function getCustomfieldMethod($cart){

		SOYShopPlugin::load("soyshop.order.customfield");

		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "list",
			"cart" => $cart
		));

		$obj = array();
		if(count($delegate->getList()) > 0){
			foreach($delegate->getList() as $list){
				if(is_array($list)){
					foreach($list as $key => $array){
						$obj[$key] = $array;
					}
				}
			}
		}
		return (count($obj) > 0) ? $obj : array();
	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors($cart){

		$this->createAdd("payment_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("payment")
		));

		if(strlen($cart->getErrorMessage("payment")) < 1)DisplayPlugin::hide("has_payment_error");

		$this->createAdd("delivery_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("delivery")
		));

		if(strlen($cart->getErrorMessage("delivery")) < 1)DisplayPlugin::hide("has_delivery_error");
	}

	/**
	 * @return boolean
	 */
	function checkError($cart){

		$res = false;

		if(!isset($_POST["payment_module"]) || strlen($_POST["payment_module"]) < 1){
			$cart->addErrorMessage("payment","支払方法が選択されていません");
			$res = true;
		}else{
			$cart->removeErrorMessage("payment");
		}

		if(!isset($_POST["delivery_module"]) || strlen($_POST["delivery_module"]) < 1){
			$cart->addErrorMessage("delivery","配送方法が選択されていません。");
			$res = true;
		}else{
			$cart->removeErrorMessage("delivery");
		}

		//Discount Module
		{
			SOYShopPlugin::load("soyshop.discount");
			$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
				"mode" => "checkError",
				"cart" => $cart,
				"param" => @$_POST["discount_module"]
			));

			if($delegate->hasError()){
				$cart->addErrorMessage("discount","割引で何らかのエラーが発生しました。");
				$res = true;
			}else{
				$cart->removeErrorMessage("discount");
			}
		}

		//Customfield Module
		{
			SOYShopPlugin::load("soyshop.order.customfield");
			$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "checkError",
				"cart" => $cart,
				"param" => @$_POST["customfield_module"]
			));

			if($delegate->hasError()){
				$cart->addErrorMessage("customfield","エラーが発生しました。");
				$res = true;
			}else{
				$cart->removeErrorMessage("customfield");
			}
		}

		return $res;
	}
}


/**
 * @class Payment_methodList
 * @generated by SOY2HTML
 */
class Payment_methodList extends HTMLList{

	private $selected = "daibiki";

	protected function populateItem($entity,$key,$counter,$length){
		$this->createAdd("payment_method","HTMLCheckBox", array(
			"name" => "payment_module",
			"value" => $key,
			"selected" => ( ($this->selected == $key) || ($length == 1) ),
			"label" => "選択する"
		));

		$this->createAdd("payment_name","HTMLLabel", array(
			"text" => $entity["name"]
		));

		$this->createAdd("payment_description","HTMLLabel", array(
			"html" => $entity["description"]
		));

		$this->createAdd("payment_charge","HTMLLabel", array(
			"text" => (isset($entity["price"])) ? soy2_number_format($entity["price"])." 円" : "",
		));
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		if(strlen($selected)){
			$this->selected = $selected;
		}
	}
}

/**
 * @class Delivery_methodList
 * @generated by SOY2HTML
 */
class Delivery_methodList extends HTMLList{
	private $selected = "yupack";

	protected function populateItem($entity,$key,$counter,$length){
		$this->createAdd("delivery_method","HTMLCheckBox", array(
			"name" => "delivery_module",
			"value" => $key,
			"selected" => ( ($this->selected == $key) || ($length == 1) ),
			"label" => "選択する"
		));

		$this->createAdd("delivery_name","HTMLLabel", array(
			"text" => $entity["name"]
		));

		$this->createAdd("delivery_description","HTMLLabel", array(
			"html" => $entity["description"]
		));

		$this->createAdd("delivery_charge","HTMLLabel", array(
			"text" => (isset($entity["price"])) ? soy2_number_format($entity["price"])." 円" : "",
		));
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		if(strlen($selected)){
			$this->selected = $selected;
		}
	}
}

/**
 * @class Discount_methodList
 */
class Discount_methodList extends HTMLList{
	protected function populateItem($entity,$key,$counter,$length){
		$this->createAdd("discount_name","HTMLLabel", array(
			"text" => $entity["name"]
		));

		$this->createAdd("discount_description","HTMLLabel", array(
			"html" => $entity["description"]
		));

		$this->createAdd("has_discount_error","HTMLModel", array(
			"visible" => strlen($entity["error"])>0
		));
		$this->createAdd("discount_error","HTMLLabel", array(
			"text" => $entity["error"]
		));
	}
}

/**
 * @class Customfield_methodList
 */
class Customfield_methodList extends HTMLList{
	protected function populateItem($entity,$key,$counter,$length){
		$this->createAdd("customfield_name","HTMLLabel", array(
			"text" => $entity["name"]
		));

		$this->createAdd("customfield_description","HTMLLabel", array(
			"html" => $entity["description"]
		));

		$this->createAdd("has_customfield_error","HTMLModel", array(
			"visible" => strlen($entity["error"])>0
		));
		$this->createAdd("customfield_error","HTMLLabel", array(
			"text" => $entity["error"]
		));
	}
}

?>
