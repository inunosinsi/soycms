<?php
include(dirname(__FILE__) . "/common.php");

class IndexPage extends WebPage{

	protected $cart;

	function doPost(){
		//あえてsoy2_check_tokenなし

		$cart = $this->cart;

		//注文日時
		if(isset($_POST["order_date"])){
			if(strlen($_POST["order_date"])){
				$cart->setOrderDate($_POST["order_date"]);
			}else{
				$cart->setOrderDate(null);
			}
			$cart->save();
		}

		//注文者
		if(isset($_POST["select_user"])){
			if(strlen($_POST["select_user"])){
				$userId = $_POST["select_user"] ;
				try{
					$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");;
					$user = $userDao->getById($userId);
					$cart->setCustomerInformation($user);
					$cart->save();
				}catch(Exception $e){
					//
				}
			}
		}
		if(isset($_POST["select_user_button"])){
			SOY2PageController::jump("Order.Register");
		}

		//まずはエラーチェックのみ
		self::checkError($cart);

		/* 古いのをクリア */
		$cart->removeModule($cart->getAttribute("payment_module"));
		$cart->clearAttribute("payment_module");
		$cart->removeModule($cart->getAttribute("delivery_module"));
		$cart->clearAttribute("delivery_module");
		SOYShopPlugin::load("soyshop.point.payment");
		SOYShopPlugin::invoke("soyshop.point.payment", array(
				"mode" => "clear",
				"cart" => $cart,
		));
		SOYShopPlugin::load("soyshop.order.customfield");
		SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "clear",
			"cart" => $cart,
		));

		//支払
		if(!$cart->hasError("payment") && isset($_POST["payment_module"])){
			//選択を保存
			$moduleId = $_POST["payment_module"];
			$cart->setAttribute("payment_module", $moduleId);

			//選択されたプラグインのみを読み込む：plugins/$moduleId/soyshop.payment.php
			$paymentModule = soyshop_get_plugin_object($moduleId);
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
			$deliveryModule = soyshop_get_plugin_object($moduleId);
			SOYShopPlugin::load("soyshop.delivery", $deliveryModule);

			//選択されたプラグインを実行
			SOYShopPlugin::invoke("soyshop.delivery", array(
				"mode" => "select",
				"cart" => $cart
			));
		}

		/**
		 * ポイント
		 * 念のため、顧客IDがあるかどうかですでに登録されているか？を見ておく
		 */
		if(!$cart->hasError("point") && (isset($_POST["point_module"])) && !is_null($cart->getCustomerInformation()->getId())){
			//全部ロードする
			SOYShopPlugin::load("soyshop.point.payment");
			SOYShopPlugin::invoke("soyshop.point.payment", array(
					"mode" => "select",
					"cart" => $cart,
					"param" => $_POST["point_module"],
					"userId" => $cart->getCustomerInformation()->getId(),
			));
		}

		//カスタムフィールド
		if(isset($_POST["customfield_module"]) || (isset($_FILES["customfield_module"]["tmp_name"]))){
			//ロードしない？
//				SOYShopPlugin::load("soyshop.order.customfield");
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "post",
				"cart" => $cart,
				"param" => $_POST["customfield_module"]
			));
		}

		//備考
		if(isset($_POST["memo"])){
			$cart->setOrderAttribute("memo", "備考", $_POST["memo"]);
			$cart->save();
		}

		//税金
		$cart->calculateConsumptionTax();

		$cart->save();
		if($cart->hasError()){
			SOY2PageController::jump("Order.Register");
		}else{
			SOY2PageController::jump("Order.Register.Confirm");
		}
	}

	private function checkError(CartLogic $cart){
		$res = false;

		if(!strlen($cart->getCustomerInformation()->getMailAddress())){
			$cart->addErrorMessage("user", "注文者を指定してください。");
			$res = true;
		}else{
			$cart->removeErrorMessage("user");
		}

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
		//Point Module
		if(isset($_POST["point_module"])){
			SOYShopPlugin::load("soyshop.point.payment");
			$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
					"mode" => "checkError",
					"cart" => $cart,
					"param" => $_POST["point_module"]
			));
			if($delegate->hasError()){
				$cart->addErrorMessage("point", MessageManager::get("POINT_ERROR"));
				$res = true;
			}else{
				$cart->removeErrorMessage("point");
			}
		}

		//Customfield Module
		SOYShopPlugin::load("soyshop.order.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "checkError",
			"cart" => $cart,
			"param" => (isset($_POST["customfield_module"])) ? $_POST["customfield_module"] : array()
		));

		if($delegate->hasError()){
			$cart->addErrorMessage("customfield", MessageManager::get("CUSTOMFIELD_ERROR"));
			$res = true;
		}else{
			$cart->removeErrorMessage("customfield");
		}

		return $res;
	}

	function __construct() {
		$this->cart = AdminCartLogic::getCart();
		$this->cart->setAttribute("page", "start");

		//注文日のデフォルトは当日
		if(!$this->cart->getOrderDate()){
			$this->cart->setOrderDate(SOY2_NOW);
		}

		parent::__construct();

		//エラー
		DisplayPlugin::toggle("order_error", count($this->cart->getErrorMessages()));

		$this->itemInfo();
		$this->dateInfo();
		$this->userInfo();
		$this->addressInfo();
		$this->memoInfo();

		$this->paymentForm();
		$this->deliveryForm();
		$this->pointForm();
		$this->orderCustomForm();
		$this->confirmForm();

		//リセットボタンの表示
		$items = $this->cart->getItems();
		$user = $this->cart->getCustomerInformation();

		DisplayPlugin::toggle("has_order", (count($items) || strlen($user->getMailAddress())));

		$this->cart->clearErrorMessage();
		$this->cart->save();
	}

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css",
			//"./js/tools/soy2_date_picker.css",
		);
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//"./js/tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}


	//商品情報
	function itemInfo(){

		//仕入値を出力するか？
		$this->addModel("is_purchase_price", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin())
		));

		$items = $this->cart->getItems();
		$cnt = count($items);

		DisplayPlugin::toggle("no_item", $cnt === 0);
		DisplayPlugin::toggle("item_info", $cnt > 0);
		DisplayPlugin::toggle("item_info1", $cnt > 0);

		include_once(dirname(__FILE__) . "/component/ItemListComponent.class.php");
		$this->createAdd("item_list", "ItemListComponent", array(
				"list" => $items,
				"cart" => $this->cart,
		));

		$this->addLabel("total_item_price", array(
				"text" => soy2_number_format($this->cart->getItemPrice())
		));

		//モジュール料金
		include_once(dirname(__FILE__) . "/component/ModuleListComponent.class.php");
		$this->createAdd("module_list", "ModuleListComponent", array(
				"list" => $this->cart->getModules()
		));

		//総額
		$this->addLabel("total_price", array(
				"text" => soy2_number_format($this->cart->getTotalPrice())
		));

		//在庫切れ
		$this->addLabel("stock_error",array(
				"text" => $this->cart->getErrorMessage("stock"),
				"visible" => strlen($this->cart->getErrorMessage("stock")),
		));
	}

	//注文日時
	function dateInfo(){

		$this->addInput("order_date", array(
				"name" => "order_date",
				"value" => $this->cart->getOrderDateText(),
				"readonly" => true
		));

		DisplayPlugin::toggle("no_order_date", !strlen($this->cart->getOrderDateText()));
		$this->addLabel("order_date_text", array(
				"text" => $this->cart->getOrderDateText(),
		));

	}

    //お客様情報
    function userInfo(){

    	//プルダウンから選択
    	$this->addForm("user_select_form", array(
    		//"action" => SOY2PageController::createLink("Order.Register.User"),
    	));

		$options = self::getUserListOptions();
    	$this->addSelect("select_user", array(
    			"name" => "select_user",
    			"options" => $options,
    			"selected" => $this->cart->getCustomerInformation()->getId(),
    	));

    	//セッションからユーザIDの取得
    	$user = $this->cart->getCustomerInformation();
		$has_user = strlen($user->getMailAddress());

		//登録あり
		DisplayPlugin::toggle("has_user", $has_user);
		DisplayPlugin::toggle("has_user2", $has_user);

		//登録無し
		DisplayPlugin::toggle("no_user", !$has_user && count($options));

		//登録済みユーザー
		DisplayPlugin::toggle("user_is_registered", strlen($user->getId()));

		//ユーザ情報がある場合
		DisplayPlugin::toggle("user_info", strlen($user->getMailAddress()));
		DisplayPlugin::toggle("user_info1", strlen($user->getMailAddress()));

    	/* 以下、ユーザー情報 */
    	$this->addLabel("user_id", array(
    		"text" => $user->getId(),
    	));
    	$this->addLink("user_detail_link", array(
    		"link" => SOY2PageController::createLink("User.Detail") . "/" . $user->getId(),
    	));

		/* 共通コンポーネント */
		SOY2::import("base.site.classes.SOYShop_UserCustomfieldList");

    	SOY2::import("component.UserComponent");
    	SOY2::import("component.backward.BackwardUserComponent");

		$backward = new BackwardUserComponent();
		$component = new UserComponent();

		$backward->backwardAdminBuildForm($this, $user);

		//共通フォーム
		$component->buildForm($this, $user, $this->cart, UserComponent::MODE_CUSTOM_CONFIRM);


		//顧客詳細が表示されない問題 直接ユニークなタグを生成する

		//メールアドレス
		$this->addLabel("mail_address_on_admin", array(
			"text" => $user->getMailAddress()
		));

		SOY2::import("domain.config.SOYShop_ShopConfig");

		//顧客コード
		DisplayPlugin::toggle("userCode", SOYShop_ShopConfig::load()->getUseUserCode());
		$this->addLabel("user_code_on_admin", array(
			"text" => $user->getUserCode()
		));

		//氏名
		$this->addLabel("name_on_admin", array(
			"text" => $user->getName(),
		));

		//フリガナ
		$this->addLabel("reading_on_admin", array(
			"text" => $user->getReading(),
		));

		//郵便番号
		$this->addLabel("zip_code_on_admin", array(
			"text" => $user->getZipCode()
		));

		//都道府県

		$this->addLabel("area_on_admin", array(
			"text" => SOYShop_Area::getAreaText($user->getArea())
		));

		//住所入力1
		$this->addLabel("address1_on_admin", array(
			"text" => $user->getAddress1(),
		));

		//住所入力2
		$this->addLabel("address2_on_admin", array(
			"text" => $user->getAddress2(),
		));

		//住所入力3
		$this->addLabel("address3_on_admin", array(
			"text" => $user->getAddress3(),
		));

		//電話番号
		$this->addLabel("telephone_number_on_admin", array(
			"text" => $user->getTelephoneNumber(),
		));

		//FAX番号
		$this->addLabel("fax_number_on_admin", array(
			"text" => $user->getFaxNumber(),
		));

		//携帯電話番号
		$this->addLabel("cellphone_number_on_admin", array(
			"text" => $user->getCellphoneNumber(),
		));

		//法人名(勤務先など)
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->addModel("is_office_item", array(
			"visible" => SOYShop_ShopConfig::load()->getDisplayUserOfficeItems()
		));
		$this->addLabel("office_on_admin", array(
			"text" => $user->getJobName()
		));
    }

	private function getUserListOptions(){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		if($userDao->countUser() > 10000) return array();	//メモリーの安全装置

		$userDao->setOrder("id asc");
		$userDao->setLimit(30);
		try{
			$__users = $userDao->getByNotDisabled();
		}catch(Exception $e){
			return array();
		}
		if(!count($__users)) return array();

		$opts = array();
		foreach($__users as $__user){
		   	$opts[$__user->getId()] = $__user->getName()." <".$__user->getMailAddress().">";
		}
		return $opts;
	}

    function addressInfo(){
    	SOY2DAOFactory::importEntity("config.SOYShop_Area");

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
		DisplayPlugin::toggle("has_send_address", $has_address);
		DisplayPlugin::toggle("has_send_address1", $has_address);

		//登録なし（注文者の住所と同じ）
		DisplayPlugin::toggle("no_send_address", !$has_address);
		DisplayPlugin::toggle("no_send_address1", !$has_address);

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
    		"text" => (isset($address["area"])) ? SOYShop_Area::getAreaText($address["area"]) : "",
    	));

    	$this->addLabel("send_address1", array(
    		"text" => (isset($address["address1"])) ? $address["address1"] : "",
    	));

    	$this->addLabel("send_address2", array(
    		"text" => (isset($address["address2"])) ? $address["address2"] : "",
    	));

		$this->addLabel("send_address3", array(
    		"text" => (isset($address["address3"])) ? $address["address3"] : "",
    	));

    	$this->addLabel("send_tel_number", array(
    		"text" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

    	$this->addLabel("send_office", array(
    		"text" => (isset($address["office"])) ? $address["office"] : "",
    	));
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

		$enabled = (count($items));// && strlen($user->getMailAddress()));

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
		$cnt = count($modules);

		DisplayPlugin::toggle("no_payment_method", $cnt === 0);
		DisplayPlugin::toggle("has_payment_method", $cnt > 0);

		include_once(dirname(__FILE__) . "/component/PaymentMethodListComponent.class.php");
		$this->createAdd("payment_method_list", "PaymentMethodListComponent", array(
			"list"     => $modules,
			"selected" => $this->cart->getAttribute("payment_module")
		));

		//エラー文言
		$error = $this->cart->getErrorMessage("payment");
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
		$cnt = count($modules);

		DisplayPlugin::toggle("no_delivery_method", $cnt === 0);
		DisplayPlugin::toggle("has_delivery_method", $cnt > 0);

		include_once(dirname(__FILE__) . "/component/DeliveryMethodListComponent.class.php");
		$this->createAdd("delivery_method_list", "DeliveryMethodListComponent", array(
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

	/**
	 * ポイントモジュール
	 */
	function pointForm(){
		SOYShopPlugin::active("soyshop.point.payment");

		$modules = $this->cart->getPointMethodList($this->cart->getCustomerInformation()->getId());

		DisplayPlugin::toggle("has_point_method", count($modules));
		DisplayPlugin::toggle("no_valid_user_for_point", !strlen($this->cart->getCustomerInformation()->getId()));
		DisplayPlugin::toggle("has_valid_user_for_point", strlen($this->cart->getCustomerInformation()->getId()));

		include_once(dirname(__FILE__) . "/component/PointMethodListComponent.class.php");
		$this->createAdd("point_method_list", "PointMethodListComponent", array(
				"list"     => $modules,
				"selected" => $this->cart->getAttribute("point_module"),
		));
	}

	/**
	 * 注文カスタムフィールド
	 */
	function orderCustomForm(){
		//アクティブなプラグインをすべて読み込む
		SOYShopPlugin::load("soyshop.order.customfield");
		$values = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "list",
			"cart" => $this->cart
		))->getList();

		$list = array();
		if(count($values)){
			foreach($values as $v){
				if(!is_array($v)) continue;
				foreach($v as $key => $obj){
					$list[$key] = $obj;
				}
			}
		}

		DisplayPlugin::toggle("has_customfield_method", count($list));

		include_once(dirname(__FILE__) . "/component/CustomfieldMethodListComponent.class.php");
		$this->createAdd("customfield_method_list", "CustomfieldMethodListComponent", array(
			"list" => $list,
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("注文を追加する", array("Order" => "注文管理"));
	}
}
