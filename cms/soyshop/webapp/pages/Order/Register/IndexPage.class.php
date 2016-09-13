<?php
include(dirname(__FILE__) . "/common.php");

class IndexPage extends WebPage{

	protected $cart;

	function doPost(){
		//あえてsoy2_check_tokenなし

		$cart = $this->cart;

		//まずはエラーチェックのみ
		if(!isset($_POST["payment_module"]) || strlen($_POST["payment_module"]) < 1){
			$cart->addErrorMessage("payment", "支払方法を選択してください。");
			$res = true;
		}else{
			$cart->removeErrorMessage("payment");
		}
		if(!isset($_POST["delivery_module"]) || strlen($_POST["delivery_module"]) < 1){
			$cart->addErrorMessage("delivery", "配送方法を選択してください。");
			$res = true;
		}else{
			$cart->removeErrorMessage("delivery");
		}

		/* 古いのをクリア */
		$cart->removeModule($cart->getAttribute("payment_module"));
		$cart->clearAttribute("payment_module");
		$cart->removeModule($cart->getAttribute("delivery_module"));
		$cart->clearAttribute("delivery_module");

		//支払
		if(!$cart->hasError("payment") && isset($_POST["payment_module"])){
			//選択を保存
			$moduleId = $_POST["payment_module"];
			$cart->setAttribute("payment_module", $moduleId);

			//選択されたプラグインのみを読み込む：plugins/$moduleId/soyshop.payment.php
			$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
			$paymentModule = $moduleDAO->getByPluginId($moduleId);
			SOYShopPlugin::load("soyshop.payment", $paymentModule);

			//選択されたプラグインを実行
			SOYShopPlugin::invoke("soyshop.payment", array(
				"mode" => "select",
				"cart" => $cart
			));
		}

		//配送
		if(!$cart->hasError("delivery") && isset($_POST["delivery_module"])){
			$moduleId = $_POST["delivery_module"];
			$cart->setAttribute("delivery_module",$moduleId);

			//選択されたプラグインのみを読み込む
			$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
			$deliveryModule = $moduleDAO->getByPluginId($moduleId);
			SOYShopPlugin::load("soyshop.delivery", $deliveryModule);

			//選択されたプラグインを実行
			SOYShopPlugin::invoke("soyshop.delivery", array(
				"mode" => "select",
				"cart" => $cart
			));
		}

		//備考
		if(isset($_POST["memo"])){
			$cart->setOrderAttribute("memo", "備考", $_POST["memo"]);
			$cart->save();
		}

		$cart->save();
		if($cart->hasError()){
			SOY2PageController::jump("Order.Register");
		}else{
			SOY2PageController::jump("Order.Register.Confirm");
		}
	}

	function __construct() {
		$this->cart = AdminCartLogic::getCart();

		WebPage::__construct();

		$this->itemInfo();
		$this->userInfo();
		$this->addressInfo();
		$this->memoInfo();

		$this->paymentForm();
		$this->deliveryForm();
		$this->confirmForm();

		$items = $this->cart->getItems();
		$user = $this->cart->getCustomerInformation();
		$hasOrder = count($items) || strlen($user->getMailAddress());
		$this->addModel("has_order", array(
			"visible" => $hasOrder,
		));
	}

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css"
		);
	}

	//商品情報
	function itemInfo(){

		$items = $this->cart->getItems();

		$this->addModel("no_item", array(
			"visible" => (count($items) < 1)
		));

		$this->addModel("item_info", array(
			"visible" => (count($items))
		));

		$this->createAdd("item_list", "ItemList", array(
			"list" => $items,
		));

		$this->addLabel("total_item_price", array(
			"text" => number_format($this->cart->getItemPrice())
		));

		//モジュール料金
		$modules = $this->cart->getModules();
		$this->createAdd("module_list", "ModuleList", array(
			"list" => $modules
		));

		//総額
		$this->addLabel("total_price", array(
			"text" => number_format($this->cart->getTotalPrice())
		));
	}

    //お客様情報
    function userInfo(){

    	//セッションからユーザIDの取得
		$user = $this->cart->getCustomerInformation();

		$has_user = strlen($user->getMailAddress());

		//登録あり
    	$this->addModel("user_info", array(
    		"visible" => $has_user
    	));

		//登録無し
    	$this->addModel("no_user", array(
    		"visible" => ! $has_user,
    	));

		//登録済みユーザー
    	$this->addModel("user_is_registered", array(
    		"visible" => strlen($user->getId()),
    	));

    	/* 以下、ユーザー情報 */
    	$this->addLabel("user_id", array(
    		"text" => $user->getId(),
    	));
    	$this->addLink("user_detail_link", array(
    		"link" => SOY2PageController::createLink("User.Detail") . "/" . $user->getId(),
    	));

    	$this->addLabel("mail_address", array(
    		"text" => $user->getMailAddress(),
    	));

    	$this->addLabel("name", array(
    		"text" => $user->getName(),
    	));

    	$this->addLabel("furigana", array(
    		"text" => $user->getReading(),
    	));

    	$this->addLabel("post_number", array(
    		"text" => $user->getZipCode()
    	));

    	$this->addLabel("area", array(
    		"text" => $user->getAreaText()
    	));

    	$this->addLabel("address1", array(
    		"text" => $user->getAddress1(),
    	));

    	$this->addLabel("address2", array(
    		"text" => $user->getAddress2(),
    	));

    	$this->addLabel("tel_number", array(
    		"text" => $user->getTelephoneNumber(),
    	));

    	$this->addLabel("fax_number", array(
    		"text" => $user->getFaxNumber(),
    	));

    	$this->addLabel("ketai_number", array(
    		"text" => $user->getCellphoneNumber(),
    	));

    	$this->addLabel("office", array(
    		"text" => $user->getJobName(),
    	));
    	
    	$this->createAdd("user_customfield_list", "_common.User.CustomFieldValueListComponent", array(
    		"list" => self::getCustomfield($user->getId())
    	));
    }
    
    private function getCustomfield($userId){
    	SOYShopPlugin::load("soyshop.user.customfield");
    	$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
    		"mode" => "order",
    		"userId" => $userId
    	));

    	$array = array();
    	foreach($delegate->getList() as $obj){
    		if(is_array($obj)){
    			foreach($obj as $value){
    				$array[] = $value;
    			}
    		}
    	}

    	return $array;
    }

    function addressInfo(){

    	//セッションから送付先情報の取得
		$address_key = $this->cart->getAttribute("address_key");
		if( isset($address_key) && is_numeric($address_key) && $address_key >= 0 ){
			$user = $this->cart->getCustomerInformation();
			$address = $user->getAddress($address_key);
		}else{
			$address = null;
		}

		$has_address = is_array($address) && isset($address["name"]) && strlen($address["name"]) ;

		//登録あり
    	$this->addModel("has_send_address", array(
    		"visible" => $has_address
    	));

		//登録なし（注文者の住所と同じ）
    	$this->addModel("no_send_address", array(
    		"visible" => ! $has_address
    	));

    	if(!is_array($address)){
    		$user = new SOYShop_User();
    		$address = $user->getEmptyAddressArray();
    	}

		/* 以下、送付先情報 */
		$this->addLabel("send_name", array(
    		"text" => (isset($address["name"])) ? $address["name"] : "",
    	));

    	$this->addLabel("send_furigana", array(
    		"text" => (isset($address["reading"])) ? $address["reading"] : "",
    	));

    	$this->addLabel("send_post_number", array(
    		"text" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
    	));

    	$this->addLabel("send_area", array(
    		"text" => (isset($address["area"])) ? $address["area"] : "",
    	));

    	$this->addLabel("send_address1", array(
    		"text" => (isset($address["address1"])) ? $address["address1"] : "",
    	));

    	$this->addLabel("send_address2", array(
    		"text" => (isset($address["address2"])) ? $address["address2"] : "",
    	));

    	$this->addLabel("send_tel_number", array(
    		"text" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

    	$this->addLabel("send_office", array(
    		"text" => (isset($address["office"])) ? $address["office"] : "",
    	));

//    	$memo = $this->cart->getOrderAttribute("memo");
//    	if(is_null($memo))$memo = array("name"=>"備考","value"=>"");
//    	$this->createAdd("order_memo","HTMLTextArea", array(
//    		"name" => "Attributes[memo]",
//    		"value" => $memo["value"]
//    	));
    }

	/**
	 * 備考
	 */
	function memoInfo(){
		$memo = $this->cart->getOrderAttribute("memo");
		$memo = isset($memo["value"]) ? $memo["value"] : "";
		$this->addTextarea("memo", array(
			"name" => "memo",
			"value" => $memo,
		));
		$this->addLabel("memo_text", array(
			"html" => nl2br(htmlspecialchars($memo, ENT_QUOTES, "UTF-8")),
		));
	}

	/**
	 * 注文実行フォーム
	 */
	function confirmForm(){

		$items = $this->cart->getItems();
		$user = $this->cart->getCustomerInformation();

		$enabled = (count($items) && strlen($user->getMailAddress()));

		$this->addForm("confirm_form", array(
			"disabled" => !$enabled,
		));

		$this->addInput("confirm_button", array(
			"type" => "submit",
			"name" => "order",
			"value" => "以上の内容で注文実行（確認）",
			"disabled" => !$enabled,
		));
	}

	/**
	 * 支払い方法選択
	 */
	function paymentForm(){
		SOYShopPlugin::active("soyshop.payment");

		$modules = $this->cart->getPaymentMethodList();

		$this->addModel("no_payment_method", array(
			"visible" => (count($modules) < 1)
		));

		$this->addModel("has_payment_method", array(
			"visible" => (count($modules))
		));

		$this->createAdd("payment_method_list", "Payment_methodList", array(
			"list"     => $modules,
			"selected" => $this->cart->getAttribute("payment_module")
		));

		//エラー文言
		$error = $this->cart->getErrorMessage("payment");
		$error = implode("\n", $this->cart->getErrorMessages());
		$this->addLabel("payment_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => (isset($error) && strlen($error))
		));
	}

	/**
	 * 配送方法選択
	 */
	function deliveryForm(){
		SOYShopPlugin::active("soyshop.delivery");

		$modules = $this->cart->getDeliveryMethodList();

		$this->addModel("no_delivery_method", array(
			"visible" => (count($modules) < 1)
		));

		$this->addModel("has_delivery_method", array(
			"visible" => (count($modules))
		));

		$this->createAdd("delivery_method_list", "Delivery_methodList", array(
			"list"     => $modules,
			"selected" => $this->cart->getAttribute("delivery_module")
		));

		//エラー文言
		$error = $this->cart->getErrorMessage("delivery");
		$this->addLabel("delivery_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => (isset($error) && strlen($error))
		));
	}
}

class Payment_methodList extends HTMLList{

	private $selected;

	protected function populateItem($entity, $key, $counter, $length){
		$this->addCheckBox("payment_method", array(
			"name" => "payment_module",
			"value" => $key,
			"selected" => ( ($this->selected == $key) || ($length == 1) ),
			"label" => (isset($entity["name"])) ? $entity["name"] : "",
		));

		$this->addLabel("payment_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : "",
		));

		$this->addLabel("payment_description", array(
			"html" => (isset($entity["description"])) ? $entity["description"] : ""
		));

		$this->addLabel("payment_charge", array(
			"text" => (isset($entity["price"]) && strlen($entity["price"])) ? number_format($entity["price"])." 円" : "",
		));
	}

	function setSelected($selected) {
		$this->selected = $selected;
	}
}

class Delivery_methodList extends HTMLList{

	private $selected;

	protected function populateItem($entity, $key, $counter, $length){
		$this->addCheckBox("delivery_method", array(
			"name" => "delivery_module",
			"value" => $key,
			"selected" => ( ($this->selected == $key) || ($length == 1) ),
			"label" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("delivery_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("delivery_description", array(
			"html" => (isset($entity["description"])) ? $entity["description"] : ""
		));

		$this->addLabel("delivery_charge", array(
			"text" => (isset($entity["price"]) &&strlen($entity["price"])) ? number_format($entity["price"])." 円" : "",
		));
	}

	function setSelected($selected) {
		$this->selected = $selected;
	}
}