<?php
include(dirname(__FILE__) . "/common.php");

class UserPage extends WebPage{

	public $component;
	public $backward;

	private $cart;
	private $item;
	private $user;
	private $session;
	private $dao;

	function doPost(){
		$cart = $this->cart;
		$next = false;

		//あえてsoy2_check_tokenなし

		//一旦リセット
		$cart->setCustomerInformation(null);

		if(isset($_POST["search_by_id"])){
			if(strlen($_POST["search_by_id"])){
				$user = self::getUserById($_POST["search_by_id"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.id", "入力されたIDに該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.id", $_POST["search_by_id"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.id", "IDを入力してください。");
				$this->session->setAttribute("order_register.input.id", $_POST["search_by_id"]);
			}
		}else if(isset($_POST["search_by_user_code"])){
			if(strlen($_POST["search_by_user_code"])){
				$user = self::getUserByUserCode($_POST["search_by_user_code"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.user_code", "入力された" . SHOP_USER_LABEL . "コードに該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.user_code", $_POST["search_by_user_code"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.user_code", SHOP_USER_LABEL . "コードを入力してください。");
				$this->session->setAttribute("order_register.input.user_code", $_POST["search_by_user_code"]);
			}
		}else if(isset($_POST["search_by_email"])){
			if(strlen($_POST["search_by_email"])){
				$user = self::getUserByEmail($_POST["search_by_email"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.email", "入力されたメールアドレスに該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.email", $_POST["search_by_email"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.email", "メールアドレスを入力してください。");
				$this->session->setAttribute("order_register.input.email", $_POST["search_by_email"]);
			}
		}else if(isset($_POST["search_by_tell"])){
			if(strlen($_POST["search_by_tell"])){
				$user = self::getUserByTell($_POST["search_by_tell"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.tell", "入力された電話番号に該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.tell", $_POST["search_by_tell"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.tell", "電話番号を入力してください。");
				$this->session->setAttribute("order_register.input.tell", $_POST["search_by_tell"]);
			}
		}else if(isset($_POST["search_by_name"])){
			if(strlen($_POST["search_by_name"])){
				$user = self::getUserByName($_POST["search_by_name"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.name", "入力された" . SHOP_USER_LABEL . "名に該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.name", $_POST["search_by_name"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.name", SHOP_USER_LABEL . "名を入力してください。");
				$this->session->setAttribute("order_register.input.name", $_POST["search_by_name"]);
			}
		}else if(isset($_POST["search_by_reading"])){
			if(strlen($_POST["search_by_reading"])){
				$user = self::getUserByReading($_POST["search_by_reading"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.reading", "入力されたフリガナに該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.reading", $_POST["search_by_reading"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.reading", "フリガナを入力してください。");
				$this->session->setAttribute("order_register.input.reading", $_POST["search_by_reading"]);
			}
		}else if(isset($_POST["Customer"]) && is_array($_POST["Customer"])){
			$user = SOY2::cast("SOYShop_User",(object)$_POST["Customer"]);

			$error = array();
			if(strlen($user->getName()) < 1) $error[] = "氏名を入力してください。";
			/** 必須は氏名だけでいい **/
			// if(strlen($user->getReading()) < 1) $error[] = "氏名（フリガナ）を入力してください。";
			// if(strlen($user->getArea()) < 1) $error[] = "住所の都道府県を選択してください。";
			// if(strlen($user->getAddress1()) < 1) $error[] = "住所を入力してください。";
			// if(strlen($user->getTelephoneNumber()) < 1) $error[] = "電話番号を入力してください。";

			if(count($error)){
				$this->session->setAttribute("order_register.error.user", implode("\n", $error));
				$this->session->setAttribute("order_register.input.user", soy2_serialize($user));
			}else{
				$cart->setCustomerInformation($user);
				$next = true;
			}
		}

		//ユーザカスタムフィールドの値をセッションに入れる
		if(isset($_POST["user_customfield"]) || isset($_POST["user_custom_search"])){
			SOYShopPlugin::load("soyshop.user.customfield");
			SOYShopPlugin::invoke("soyshop.user.customfield",array(
				"mode" => "post",
				"app" => $this->cart,
				"param" => $_POST["user_customfield"]
			));
		}

		if($next){
			$cart->save();
			SOY2PageController::jump("Order.Register");
		}else{
			SOY2PageController::jump("Order.Register.User");
		}
	}

	function __construct($args) {
		$this->cart = AdminCartLogic::getCart();
		$this->session = SOY2ActionSession::getUserSession();
		$this->dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		/* 共通コンポーネント */
		SOY2::import("base.site.classes.SOYShop_UserCustomfieldList");
    	SOY2::import("component.UserComponent");
    	SOY2::import("component.backward.BackwardUserComponent");

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		//パラメータからユーザIDの取得
		$userId = (isset($args[0])) ? (int)$args[0] : null;
		if(isset($args[0]) && strlen($args[0])){
			$userId = (int)$args[0];
			try{
				$user = self::getUserById($userId);
				$this->cart->setCustomerInformation($user);
				$this->cart->save();
				SOY2PageController::jump("Order.Register");
			}catch(Exception $e){
			}
		}


		//入力値を呼び出す
		$user = $this->session->getAttribute("order_register.input.user");
		if(strlen($user)){
			$user = soy2_unserialize($user);
		}
		if( ! $user instanceof SOYShop_User){
			$user = new SOYShop_User();
		}

		parent::__construct();

		foreach(array("id", "user_code", "email", "tell", "name", "reading") as $t){
			$this->addForm("user_search_by_" . $t . "_form");

			//エラー文言
			$error = $this->session->getAttribute("order_register.error." . $t);
			$this->addLabel("search_by_" . $t . "_error", array(
				"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
				"visible" => isset($error) && strlen($error)
			));

			//クリア
			$this->session->setAttribute("order_register.input." . $t, null);
			$this->session->setAttribute("order_register.error." . $t, null);
		}

		self::userForm($user);

		//エラー文言
		$error = $this->session->getAttribute("order_register.error.user");
		$this->addLabel("register_user_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => isset($error) && strlen($error)
		));

		//クリア
		$this->session->setAttribute("order_register.input.user", null);
		$this->session->setAttribute("order_register.error.user", null);

		$this->addModel("zip2address_js", array(
			"src" => soyshop_get_zip_2_address_js_filepath()
		));
   }

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css"
		);
	}

	//お客様情報入力画面
	private function userForm(SOYShop_User $user){

		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();

		//ダミーの住所挿入ボタン
		DisplayPlugin::toggle("dummy_address_button", $config->getInsertDummyAddressOnAdmin());

		foreach(array("id", "user_code", "email", "tell", "name", "reading") as $t){
			$this->addInput("search_by_" . $t, array(
				"name" => "search_by_" . $t,
				"value" => $this->session->getAttribute("order_register.input." . $t),
			));
		}

		$mailAddress = $user->getMailAddress();
		if(!strlen($mailAddress) && $config->getInsertDummyMailAddressOnAdmin()){
			$mailAddress = soyshop_dummy_mail_address();
		}

		$this->addForm("user_create_form");

		//以前のフォーム 後方互換
		$this->backward->backwardAdminBuildForm($this, $user);

		//共通フォーム
		$this->component->buildForm($this, $user, $this->cart, UserComponent::MODE_CUSTOM_FORM);

		//項目の非表示用タグ
		foreach(SOYShop_ShopConfig::load()->getCustomerAdminConfig() as $key => $bool){
			if($key == "accountId" && $bool){
				//ログインIDのみ、マイページでログインIDを使用する時だけtrueにする
				$bool = (SOYShop_ShopConfig::load()->getAllowLoginIdLogin() != 0);
			}
			DisplayPlugin::toggle($key, $bool);
		}

		//法人名(勤務先など)
		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("office_item", SOYShop_ShopConfig::load()->getDisplayUserOfficeItems());

    	$this->addInput("office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    		"size" => 60,
    	));
	}


	private function getUserById($userId){
		try{
			return $this->dao->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}

	private function getUserByUserCode($userCode){
		try{
			return $this->dao->getByUserCode($userCode);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}

	private function getUserByEmail($email){
		try{
			return $this->dao->getByMailAddress($email);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	private function getUserByTell($tell){
		$tell = str_replace(array("-", "ー", "−"), "", $tell);

		//すべての顧客IDと電話番号を取得
		$sql = "SELECT id, telephone_number FROM soyshop_user WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " AND telephone_number IS NOT NULL AND telephone_number != ''";
		try{
			$res = $this->dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return new SOYShop_User();

		foreach($res as $v){
			$t = str_replace(array("-", "ー", "−"), "", $v["telephone_number"]);

			if($tell == $t){
				try{
					return $this->dao->getById($v["id"]);
				}catch(Exception $e){
					//
				}
			}
		}

		return new SOYShop_User();
	}

	private function getUserByName($name){
		$strings = self::str2array($name);
		if(!count($strings)) return new SOYShop_User();

		$dao = $this->dao;
		$sql = "SELECT id FROM soyshop_user " .
				"WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ";
		foreach($strings as $str){
			$sql .= "AND name LIKE '%" . htmlspecialchars($str, ENT_QUOTES, "UTF-8") . "%' ";
		}
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return new SOYShop_User();
		foreach($res as $v){
			try{
				return $dao->getById($v["id"]);
			}catch(Exception $e){
				//
			}
		}

		return new SOYShop_User();
	}

	private function getUserByReading($reading){
		$strings = self::str2array($reading);

		if(!count($strings)) return new SOYShop_User();

		$sql = "SELECT id FROM soyshop_user " .
				"WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ";
		foreach($strings as $str){
			$sql .= "AND reading LIKE '%" . htmlspecialchars($str, ENT_QUOTES, "UTF-8") . "%' ";
		}
		try{
			$res = $this->dao->executeQuery($sql);
		}catch(Exception $e){
			return new SOYShop_User();
		}

		if(!count($res)) return new SOYShop_User();
		foreach($res as $v){
			try{
				return $this->dao->getById($v["id"]);
			}catch(Exception $e){
				//
			}
		}

		return new SOYShop_User();
	}

	private function str2array($str){
		//全角スペースを半角スペースにする
		return explode(" ", str_replace("　", " ", $str));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("注文社を指定する", array("Order" => "注文管理", "Order.Register" => "注文を追加する"));
	}
}
