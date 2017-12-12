<?php
include(dirname(__FILE__) . "/common.php");

class UserPage extends WebPage{

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
				$user = $this->getUserById($_POST["search_by_id"]);
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
		}else if(isset($_POST["search_by_email"])){
			if(strlen($_POST["search_by_email"])){
				$user = $this->getUserByEmail($_POST["search_by_email"]);
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
				$user = $this->getUserByTell($_POST["search_by_tell"]);
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
				$user = $this->getUserByName($_POST["search_by_name"]);
				if(strlen($user->getId())){
					//OK
					$cart->setCustomerInformation($user);
					$next = true;
				}else{
					//NG
					$this->session->setAttribute("order_register.error.name", "入力された顧客名に該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.name", $_POST["search_by_name"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.name", "顧客名を入力してください。");
				$this->session->setAttribute("order_register.input.name", $_POST["search_by_name"]);
			}
		}else if(isset($_POST["search_by_reading"])){
			if(strlen($_POST["search_by_reading"])){
				$user = $this->getUserByReading($_POST["search_by_reading"]);
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
			if(strlen($user->getReading()) < 1) $error[] = "氏名（フリガナ）を入力してください。";
			if(strlen($user->getArea()) < 1) $error[] = "住所の都道府県を選択してください。";
			if(strlen($user->getAddress1()) < 1) $error[] = "住所を入力してください。";
			if(strlen($user->getTelephoneNumber()) < 1) $error[] = "電話番号を入力してください。";

			if(count($error)){
				$this->session->setAttribute("order_register.error.user", implode("\n", $error));
				$this->session->setAttribute("order_register.input.user", soy2_serialize($user));
			}else{
				$cart->setCustomerInformation($user);
				$next = true;
			}
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
		$this->dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");;

		//パラメータからユーザIDの取得
		$userId = (isset($args[0])) ? (int)$args[0] : null;
		if(isset($args[0]) && strlen($args[0])){
			$userId = (int)$args[0];
			try{
				$user = $this->getUserById($userId);
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

		foreach(array("id", "email", "tell", "name", "reading") as $t){
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
		self::addressForm($user);

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
			"src" => soyshop_get_site_url() . "themes/common/js/zip2address.js"
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

		foreach(array("id", "email", "tell", "name", "reading") as $t){
			$this->addInput("search_by_" . $t, array(
				"name" => "search_by_" . $t,
				"value" => $this->session->getAttribute("order_register.input." . $t),
			));
		}

		$mailAddress = $user->getMailAddress();
		if(!strlen($mailAddress)){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			if(SOYShop_ShopConfig::load()->getInsertDummyMailAddressOnAdmin()){
				$mailAddress = soyshop_dummy_mail_address();
			}
		}

		$this->addForm("user_create_form");

		//新規登録フォーム
		$this->addInput("mail_address", array(
			"name" => "Customer[mailAddress]",
			"value" => $mailAddress,
		));

//		$this->createAdd("password","HTMLInput", array(
//			"name" => "Customer[password]",
//			"value" => $user->getPassword(),
//		));

		$this->addInput("name", array(
			"name" => "Customer[name]",
			"value" => $user->getName(),
		));

		$this->addInput("reading", array(
			"name" => "Customer[reading]",
			"value" => $user->getReading(),
		));

		$this->addCheckBox("gender_male", array(
			"type" => "radio",
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 OR $user->getGender() === "0") ? true : false
		));

		$this->addCheckBox("gender_female", array(
			"type" => "radio",
			"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 OR $user->getGender() === "1") ? true : false
		));

		$this->addInput("birth_year", array(
			"name" => "Customer[birthday][]",
			"value" => $user->getBirthdayYear(),
		));

		$this->addInput("birth_month", array(
			"name" => "Customer[birthday][]",
			"value" => $user->getBirthdayMonth(),
		));

		$this->addInput("birth_day", array(
			"name" => "Customer[birthday][]",
			"value" => $user->getBirthdayDay(),
		));

		$this->addInput("post_number", array(
			"name" => "Customer[zipCode]",
			"value" => $user->getZipCode()
		));

		$this->addSelect("area", array(
			"name" => "Customer[area]",
			"options" => SOYShop_Area::getAreas(),
			"value" => $user->getArea()
		));

		$this->addInput("address1", array(
			"name" => "Customer[address1]",
			"value" => $user->getAddress1(),
		));

		$this->addInput("address2", array(
			"name" => "Customer[address2]",
			"value" => $user->getAddress2(),
		));

		$this->addInput("tel_number", array(
			"name" => "Customer[telephoneNumber]",
			"value" => $user->getTelephoneNumber(),
		));

		$this->addInput("fax_number", array(
			"name" => "Customer[faxNumber]",
			"value" => $user->getFaxNumber(),
		));

		$this->addInput("ketai_number", array(
			"name" => "Customer[cellphoneNumber]",
			"value" => $user->getCellphoneNumber(),
		));

		$this->addInput("office", array(
			"name" => "Customer[jobName]",
			"value" => $user->getJobName(),
		));
	}

	private function addressForm(SOYShop_User $user){
		$address = $user->getEmptyAddressArray();

		$this->addInput("send_name", array(
			"name" => "Address[name]",
			"value" => (isset($address["name"])) ? $address["name"] : "",
		));

		$this->addInput("send_furigana", array(
			"name" => "Address[reading]",
			"value" => (isset($address["reading"])) ? $address["reading"] : "",
		));

		$this->addInput("send_post_number", array(
			"name" => "Address[zipCode]",
			"value" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
		));

		$this->addSelect("send_area", array(
			"name" => "Address[area]",
			"options" => SOYShop_Area::getAreas(),
			"value" => (isset($address["area"])) ? $address["area"] : null,
		));

		$this->addInput("send_address1", array(
			"name" => "Address[address1]",
			"value" => (isset($address["address1"])) ? $address["address1"] : "",
		));

		$this->addInput("send_address2", array(
			"name" => "Address[address2]",
			"value" => (isset($address["address2"])) ? $address["address2"] : "",
		));

		$this->addInput("send_tel_number", array(
			"name" => "Address[telephoneNumber]",
			"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
		));

		$this->addInput("send_office", array(
			"name" => "Address[office]",
			"value" => (isset($address["office"])) ? $address["office"] : "",
		));
	}

	function getUserById($userId){
		$dao = $this->dao;
		try{
			$user = $dao->getById($userId);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
		return $user;
	}

	function getUserByEmail($email){
		$dao = $this->dao;
		try{
			$user = $dao->getByMailAddress($email);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
		return $user;
	}
	function getUserByTell($tell){
		$tell = str_replace(array("-", "ー", "−"), "", $tell);

		$dao = $this->dao;
		//すべての顧客IDと電話番号を取得
		$sql = "SELECT id, telephone_number FROM soyshop_user WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " AND telephone_number IS NOT NULL AND telephone_number != ''";
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return new SOYShop_User();

		foreach($res as $v){
			$t = str_replace(array("-", "ー", "−"), "", $v["telephone_number"]);

			if($tell == $t){
				try{
					return $dao->getById($v["id"]);
				}catch(Exception $e){
					//
				}
			}
		}

		return new SOYShop_User();
	}

	function getUserByName($name){
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

	function getUserByReading($reading){
		$strings = self::str2array($reading);

		if(!count($strings)) return new SOYShop_User();

		$dao = $this->dao;
		$sql = "SELECT id FROM soyshop_user " .
				"WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ";
		foreach($strings as $str){
			$sql .= "AND reading LIKE '%" . htmlspecialchars($str, ENT_QUOTES, "UTF-8") . "%' ";
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

	private function str2array($str){
		//全角スペースを半角スペースにする
		$str = str_replace("　", " ", $str);

		return explode(" ", $str);
	}
}
