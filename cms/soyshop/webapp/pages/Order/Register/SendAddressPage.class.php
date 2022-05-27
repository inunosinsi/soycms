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

			$err = array();

			SOY2::import("domain.config.SOYShop_ShopConfig");
			$requiredCnf = SOYShop_ShopConfig::load()->getSendAddressInformationConfig();

			foreach($requiredCnf as $key => $bool){
				if(!$bool || $key == "address") continue;
				$address[$key] = trim($address[$key]);
				if(strlen($address[$key]) < 1){
					switch($key){
						case "name":
							$err[] = "氏名を入力してください。";
							break;
						case "reading":
							$err[] = "フリガナを入力してください。";
							break;
						case "telephoneNumber":
							$err[] = "電話番号を入力してください。";
							break;
					}
				}	
			}

			if(isset($requiredCnf["address"]) && $requiredCnf["address"]){
				if(strlen($address["zipCode"]) < 1) $err[] = "郵便番号を入力してください。";
				if(strlen($address["area"]) < 1) $err[] = "住所の都道府県を選択してください。";
			}
			
			
			/** 住所のエラー判定を設ける **/
			SOY2::import("util.SOYShopAddressUtil");
			$addressItems = SOYShopAddressUtil::getAddressItems();
			if(isset($addressItems[0]) && isset($addressItems[0]["label"]) && strlen($addressItems[0]["label"])){
				if(isset($addressItems[0]["required"]) && $addressItems[0]["required"]){
					if(strlen($address["address1"]) < 1) $err[] = "住所を入力してください。";
				}
			}

			$next = false;
			if(count($err)){
				$this->session->setAttribute("order_register.error.send_address", implode("\n", $err));
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
		if(is_string($address) && strlen($address)) $address = soy2_unserialize($address);
		if(!is_array($address)) $address = soyshop_get_user_object(0)->getEmptyAddressArray();

    	parent::__construct();

    	$this->addForm("address_form");

    	self::_buildAddressForm($address);
		self::_buildCustomerInformationArea();

		//エラー文言
		$err = $this->session->getAttribute("order_register.error.send_address");
		if(!is_string($err)) $err = "";
		$this->addLabel("error", array(
			"html" => nl2br(htmlspecialchars($err, ENT_QUOTES, "UTF-8")),
			"visible" => (strlen($err))
		));

		//クリア
		$this->session->setAttribute("order_register.input.send_address", null);
		$this->session->setAttribute("order_register.error.send_address", null);

		$this->addModel("zip2address_js", array(
			"src" => soyshop_get_site_url() . "themes/common/js/zip2address.js"
		));
   }


    private function _buildAddressForm(array $address){

		$this->addInput("name", array(
    		"name" => "Address[name]",
    		"value" => (isset($address["name"])) ? $address["name"] : "",
    	));

    	$this->addInput("reading", array(
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

		SOY2::import("util.SOYShopAddressUtil");
		$addressItems = SOYShopAddressUtil::getAddressItems();
		for($i = 1; $i <= 4; $i++){
			$itemCnf = (isset($addressItems[$i - 1])) ? $addressItems[$i - 1] : SOYShopAddressUtil::getEmptyAddressItem();

			$this->addModel("address" . $i . "_show", array(
				"visible" => (isset($itemCnf["label"]) && strlen($itemCnf["label"]))
			));

			$this->addInput("address" . $i, array(
	    		"name" => "Address[address" . $i . "]",
	    		"value" => (isset($address["address" . $i])) ? $address["address" . $i] : "",
	    	));
		}

    	$this->addInput("telephone_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

		//法人名(勤務先など)
		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("office_item", SOYShop_ShopConfig::load()->getDisplayUserOfficeItems());

    	$this->addInput("office", array(
    		"name" => "Address[office]",
    		"value" => (isset($address["office"])) ? $address["office"] : "",
    	));
    }

	private function _buildCustomerInformationArea(){
		$user = AdminCartLogic::getCart()->getCustomerInformation();
		
		$this->addInput("customer_name_hidden", array(
			"value" => $user->getName()
		));

		$this->addInput("customer_reading_hidden", array(
			"value" => $user->getReading()
		));

		$this->addInput("customer_telephone_number_hidden", array(
			"value" => $user->getTelephoneNumber()
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("送付先を指定する", array("Order" => "注文管理", "Order.Register" => "注文を追加する"));
	}
}
