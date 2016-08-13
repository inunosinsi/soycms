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
					$this->session->setAttribute("order_register.error.user_id", "入力されたIDに該当するユーザーが見つかりません。");
					$this->session->setAttribute("order_register.input.user_id", $_POST["search_by_id"]);
				}
			}else{
				//NG
				$this->session->setAttribute("order_register.error.user_id", "IDを入力してください。");
				$this->session->setAttribute("order_register.input.user_id", $_POST["search_by_id"]);
			}
		}elseif(isset($_POST["search_by_email"])){
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
		}elseif(isset($_POST["Customer"]) && is_array($_POST["Customer"])){
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

    	WebPage::__construct();

    	$this->addForm("user_search_by_id_form");
    	$this->addForm("user_search_by_email_form");
    	$this->addForm("user_create_form");

    	$this->userForm($user);

    	$this->addressForm($user);

		//エラー文言
		$error = $this->session->getAttribute("order_register.error.email");
		$this->addLabel("search_by_email_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => isset($error) && strlen($error)
		));
		$error = $this->session->getAttribute("order_register.error.user_id");
		$this->addLabel("search_by_id_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => isset($error) && strlen($error)
		));
		$error = $this->session->getAttribute("order_register.error.user");
		$this->addLabel("register_user_error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => isset($error) && strlen($error)
		));

		//クリア
		$this->session->setAttribute("order_register.input.user", null);
		$this->session->setAttribute("order_register.error.user", null);
		$this->session->setAttribute("order_register.input.email", null);
		$this->session->setAttribute("order_register.error.email", null);
		$this->session->setAttribute("order_register.input.user_id", null);
		$this->session->setAttribute("order_register.error.user_id", null);
   }

	function getCSS(){
		return array(
			"./css/admin/user_detail.css",
			"./css/admin/order_register.css"
		);
	}

    //お客様情報入力画面
    function userForm(SOYShop_User $user){

		//IDで検索
		$this->addInput("search_by_id", array(
			"name" => "search_by_id",
			"value" => $this->session->getAttribute("order_register.input.user_id"),
		));

		//メールアドレスで検索
		$this->addInput("search_by_email", array(
			"name" => "search_by_email",
			"value" => $this->session->getAttribute("order_register.input.email"),
		));

		//新規登録フォーム
		$this->addInput("mail_address", array(
			"name" => "Customer[mailAddress]",
			"value" => $user->getMailAddress(),
		));

//    	$this->createAdd("password","HTMLInput", array(
//    		"name" => "Customer[password]",
//    		"value" => $user->getPassword(),
//    	));

		$this->addInput("name", array(
			"name" => "Customer[name]",
			"value" => $user->getName(),
		));

    	$this->addInput("furigana", array(
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

    function addressForm(SOYShop_User $user){
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
}
