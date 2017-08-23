<?php
SOY2::import("domain.user.SOYShop_User");
include(dirname(__FILE__) . "/common.php");

class SendAddressPage extends WebPage{

	private $session;

	function doPost(){
		$next = false;

		//あえてsoy2_check_tokenなし

		if(isset($_POST["Address"]) && is_array($_POST["Address"])){
			$address = $_POST["Address"];

			$error = array();
			if(strlen($address["name"]) < 1) $error[] = "氏名を入力してください。";
			if(strlen($address["reading"]) < 1) $error[] = "氏名（フリガナ）を入力してください。";
			if(strlen($address["area"]) < 1) $error[] = "住所の都道府県を選択してください。";
			if(strlen($address["address1"]) < 1) $error[] = "住所を入力してください。";
			if(strlen($address["telephoneNumber"]) < 1) $error[] = "電話番号を入力してください。";

			if(count($error)){
				$this->session->setAttribute("order_register.error.send_address", implode("\n", $error));
				$this->session->setAttribute("order_register.input.send_address", soy2_serialize($address));
			}else{
				$cart = AdminCartLogic::getCart();
				$user = $cart->getCustomerInformation();
				$user->setAddressList(array($address));
				$cart->setAttribute("address_key", 0);
				$cart->save();
				$next = true;
			}
		}

		if($next){
			SOY2PageController::jump("Order.Register");
		}else{
			SOY2PageController::jump("Order.Register.SendAddress");
		}
	}

    function __construct($args) {
    	$this->session = SOY2ActionSession::getUserSession();

		//入力値を呼び出す
		$address = $this->session->getAttribute("order_register.input.send_address");
		if(strlen($address)){
			$address = soy2_unserialize($address);
		}
		if( ! is_array($address)){
	    	$user = new SOYShop_User();
	    	$address = $user->getEmptyAddressArray();
		}

    	parent::__construct();

    	$this->addForm("address_form");

    	$this->addressForm($address);

		//エラー文言
		$error = $this->session->getAttribute("order_register.error.send_address");
		$this->addLabel("error", array(
			"html" => nl2br(htmlspecialchars($error, ENT_QUOTES, "UTF-8")),
			"visible" => isset($error) && strlen($error)
		));

		//クリア
		$this->session->setAttribute("order_register.input.send_address", null);
		$this->session->setAttribute("order_register.error.send_address", null);

   }


    function addressForm($address){

		$this->addInput("name", array(
    		"name" => "Address[name]",
    		"value" => (isset($address["name"])) ? $address["name"] : "",
    	));

    	$this->addInput("furigana", array(
    		"name" => "Address[reading]",
    		"value" => (isset($address["reading"])) ? $address["reading"] : "",
    	));

    	$this->addInput("post_number", array(
    		"name" => "Address[zipCode]",
    		"value" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
    	));

    	$this->addSelect("area", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => (isset($address["area"])) ? $address["area"] : null,
    	));

    	$this->addInput("address1", array(
    		"name" => "Address[address1]",
    		"value" => (isset($address["address1"])) ? $address["address1"] : "",
    	));

    	$this->addInput("address2", array(
    		"name" => "Address[address2]",
    		"value" => (isset($address["address2"])) ? $address["address2"] : "",
    	));

    	$this->addInput("tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

    	$this->addInput("office", array(
    		"name" => "Address[office]",
    		"value" => (isset($address["office"])) ? $address["office"] : "",
    	));

//    	$memo = $cart->getOrderAttribute("memo");
//    	if(is_null($memo))$memo = array("name"=>"備考","value"=>"");
//    	$this->createAdd("order_memo","HTMLTextArea", array(
//    		"name" => "Attributes[memo]",
//    		"value" => $memo["value"]
//    	));
    }
}
