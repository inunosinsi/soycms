<?php
/**
 * @class Cart02Page
 * @date 2009-07-16T16:25:28+09:00
 * @author SOY2HTMLFactory
 */
class Cart02Page extends MobileCartPageBase{

	function doPost(){

		if(isset($_POST["next"]) || isset($_POST["next_x"])){

			//パスワードチェック用
			$passwordCheck = true;

			$cart = CartLogic::getCart();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			$user = new SOYShop_User();

			//POSTデータ
			$customer = $_POST["Customer"];
			$customer["name"] = $this->_trim($customer["name"]);
			$customer["reading"] = $this->convertKana($customer["reading"]);
			
			$customer = (object)$customer;
			
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($customer as $key => $value){
					$array[$key] = mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$customer = (object)$array;
			}
			
			$postUser = SOY2::cast("SOYShop_User",$customer);

			//ログインしている場合
			try{
				if($cart->getAttribute("logined")){
					$user = $cart->getCustomerInformation();
					$user = $userDAO->getById($cart->getAttribute("logined_userid"));
					$postUser->setPassword(null);

				}else{
					$tmpUser = $userDAO->getByMailAddress($postUser->getMailAddress());
					$postUser->setId($tmpUser->getId());

					/*
					 * パスワードを入力しているが
					 * 間違っていた場合
					 */
					if(strlen($postUser->getPassword())>0 && strlen($tmpUser->getPassword()) > 0){
						$password = $postUser->getPassword();

						if($tmpUser->checkPassword($password)){
							$user = $tmpUser;
							$cart->setAttribute("logined", true);
							$postUser->setPassword(null);

						}else{
							//エラーメッセージ表示
							$cart->addErrorMessage("password_error","不正なパスワードです。");
							$passwordCheck = false;
						}
					}
				}
			}catch(Exception $e){

			}

			//POSTの値を上書き
			SOY2::cast($user,$postUser);

			/*
			 * 宛先
			 */
			$address = $_POST["Address"];
			
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($address as $key => $value){
					$array[$key] = mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$address = $array;
			}
			
			$res = $user->checkValidAddress($address);
			$validAddress = true;

			if($res < 0){
				//顧客の連絡先を使う
				$cart->clearAttribute("address_key");
			}else{

				if(!$res){
					$validAddress = false;
					$cart->addErrorMessage("send_address","宛先を正しく設定してください。");
				}

				$cart->setAttribute("address_key", 0);
				$user->setAddressList(array($address));
			}

			$cart->setCustomerInformation($user);

			//備考
			if(isset($_POST["Attributes"]) && isset($_POST["Attributes"]["memo"])){
				$memo = $_POST["Attributes"]["memo"];
				if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
					$memo = mb_convert_encoding($memo,"UTF-8","SJIS");			
				}
				$cart->setOrderAttribute("memo","備考",$memo);
			}else{

			}

			$cart->save();
			
			if($validAddress
				&& $passwordCheck && false == $this->checkError($cart,$address)){
				$cart->setAttribute("page", "Cart03");
			}else{
				$cart->setAttribute("page", "Cart02");
			}
			
			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
			}
			soyshop_redirect_cart($param);
		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart01");

			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
			}
			soyshop_redirect_cart($param);
		}
		
		//郵便番号での住所検索
		if(isset($_POST["user_zip_search"]) || isset($_POST["send_zip_search"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");
			$cart = CartLogic::getCart();

			$customer = (object)$_POST["Customer"];
			
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($customer as $key => $value){
					$array[$key] = mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$customer = (object)$array;
			}
						
			$user = SOY2::cast("SOYShop_User",$customer);
			
			//宛先
			$address = $_POST["Address"];
			
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($address as $key => $value){
					$array[$key] = mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$address = $array;
			}

			//備考
			if(isset($_POST["Attributes"]) && isset($_POST["Attributes"]["memo"])){
				$memo = $_POST["Attributes"]["memo"];
				if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
					$memo = mb_convert_encoding($memo,"UTF-8","SJIS");			
				}
				$cart->setOrderAttribute("memo","備考",$memo);
			}else{

			}
					
			if(isset($_POST["user_zip_search"])){
				$code = soyshop_cart_address_validate($user->getZipcode());
				$res = $logic->search($code);

				$user->setArea(SOYShop_Area::getAreaByText($res["prefecture"]));
				$user->setAddress1($res["address1"]);
				$user->setAddress2($res["address2"]);
				$anchor = "zipcode1";
				
			}else{
				$code = soyshop_cart_address_validate($address["zipCode"]);
				$res = $logic->search($code);

				$address["area"] = SOYShop_Area::getAreaByText($res["prefecture"]);
				$address["address1"] = $res["address1"];
				$address["address2"] = $res["address2"];
				$anchor = "zipcode2";
			}
			
			$cart->setAttribute("address_key", 0);
			$user->setAddressList(array($address));
			$cart->setCustomerInformation($user);
			$cart->save();
			
			
			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
				soyshop_redirect_cart($param);
			}
			
			soyshop_redirect_cart_with_anchor($anchor);			
		}
	}

	function Cart02Page(){
		parent::__construct();
		
		$url = soyshop_get_cart_url(false);
		if(isset($_GET[session_name()])){
			$url = $url."?".session_name() . "=" . session_id();
		}		

		$this->createAdd("order_form","HTMLForm", array(
			"action" => $url,
		));

		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$this->createAdd("item_list", "_common.ItemList", array(
			"list" => $items
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$customer = $cart->getCustomerInformation();

		//顧客情報フォーム
		$this->buildForm($cart,$customer);

		//送付先フォーム
		$this->buildSendForm($cart,$customer);

		//エラー周り
		DisplayPlugin::toggle("has_error",$cart->hasError());
		$this->appendErrors($cart);
	}

	function buildForm($cart,$user){
		
		$this->createAdd("mail_address","HTMLInput", array(
    		"name" => "Customer[mailAddress]",
    		"value" => $user->getMailAddress(),
    	));

    	$this->createAdd("password_input","HTMLModel", array(
    		"visible" => (!$cart->getAttribute("logined")),
    	));

    	$this->createAdd("new_password","HTMLModel", array(
    		"visible" => ($cart->getAttribute("logined")),
    	));

    	$this->createAdd("password","HTMLInput", array(
    		"name" => "Customer[password]",
    		"value" => $user->getPassword(),
    	));

    	$this->createAdd("name","HTMLInput", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));

    	$this->createAdd("furigana","HTMLInput", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

    	$this->createAdd("gender_male","HTMLCheckbox", array(
    		"type" => "radio",
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 OR $user->getGender() === "0") ? true : false
    	));

    	$this->createAdd("gender_female","HTMLCheckbox", array(
    		"type" => "radio",
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 OR $user->getGender() === "1") ? true : false
    	));


    	$this->createAdd("birth_year","HTMLInput", array(
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayYear(),
    	));

    	$this->createAdd("birth_month","HTMLInput", array(
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayMonth(),
    	));

    	$this->createAdd("birth_day","HTMLInput", array(
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayDay(),
    	));

    	$this->createAdd("post_number","HTMLInput", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode()
    	));

    	$this->createAdd("area","HTMLSelect", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $user->getArea()
    	));

    	$this->createAdd("address1","HTMLInput", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

    	$this->createAdd("address2","HTMLInput", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));

    	$this->createAdd("tel_number","HTMLInput", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

    	$this->createAdd("fax_number","HTMLInput", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

    	$this->createAdd("ketai_number","HTMLInput", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

    	$this->createAdd("office","HTMLInput", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));
	}

	function buildSendForm($cart,$customer){

		$address = ($cart->isUseCutomerAddress()) ? $cart->getAddress() : $cart->getCustomerInformation()->getEmptyAddressArray();

		$this->createAdd("send_name","HTMLInput", array(
    		"name" => "Address[name]",
    		"value" => $address["name"],
    	));

    	$this->createAdd("send_furigana","HTMLInput", array(
    		"name" => "Address[reading]",
    		"value" => $address["reading"],
    	));

    	$this->createAdd("send_post_number","HTMLInput", array(
    		"name" => "Address[zipCode]",
    		"value" => $address["zipCode"],
    	));

    	$this->createAdd("send_area","HTMLSelect", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $address["area"],
    	));

    	$this->createAdd("send_address1","HTMLInput", array(
    		"name" => "Address[address1]",
    		"value" => $address["address1"],
    	));

    	$this->createAdd("send_address2","HTMLInput", array(
    		"name" => "Address[address2]",
    		"value" => $address["address2"],
    	));

    	$this->createAdd("send_tel_number","HTMLInput", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => $address["telephoneNumber"],
    	));

    	$this->createAdd("send_office","HTMLInput", array(
    		"name" => "Address[office]",
    		"value" => @$address["office"],
    	));

    	$memo = $cart->getOrderAttribute("memo");
    	if(is_null($memo))$memo = array("name"=>"備考","value"=>"");
    	$this->createAdd("order_memo","HTMLTextArea", array(
    		"name" => "Attributes[memo]",
    		"value" => $memo["value"]
    	));
	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors($cart){

		$this->createAdd("mail_address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("mail_address")
		));

		$this->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("name")
		));

		$this->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("reading")
		));

		$this->createAdd("zip_code_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("zip_code")
		));

		$this->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("address")
		));

		$this->createAdd("tel_number_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("tel_number")
		));

		$this->createAdd("send_address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("send_address")
		));

		$this->createAdd("has_send_address_error","HTMLModel", array(
			"visible" => (strlen($cart->getErrorMessage("send_address")) > 0)
		));

		$this->createAdd("password_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("password_error")
		));

		$this->createAdd("password_invalid", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("password_error")
		));

	}

	/**
	 * @return boolean
	 */
	function checkError($cart,$address){

		$res = false;
		$cart->clearErrorMessage();

		if(tstrlen($cart->getCustomerInformation()->getMailAddress()) < 1){
			$cart->addErrorMessage("mail_address","メールアドレスを入力してください。");
			$res = true;
		}else if(!isValidEmail($cart->getCustomerInformation()->getMailAddress())){
			$cart->addErrorMessage("mail_address","メールアドレスの書式が不正です。");
			$res = true;
		}

		if(tstrlen($cart->getCustomerInformation()->getName()) < 1){
			$cart->addErrorMessage("name","お名前を入力してください。");
			$res = true;
		}

		$reading = str_replace(array(" ","　"),"",$cart->getCustomerInformation()->getReading());
		if(tstrlen($reading) < 1){
			$cart->addErrorMessage("reading","お名前のフリガナを入力してください。");
			$res = true;
		}
		
		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])","",$reading)) !== 0){
			$cart->addErrorMessage("reading","お名前のフリガナをカタカナで入力してください。");
			$res = true;
		}

		if(tstrlen($cart->getCustomerInformation()->getZipCode()) < 1){
			$cart->addErrorMessage("zip_code","郵便番号を入力してください。");
			$res = true;
		}

		if(tstrlen($cart->getCustomerInformation()->getArea())<1 || tstrlen($cart->getCustomerInformation()->getAddress1()) < 1){
			$cart->addErrorMessage("address","住所を入力してください。");
			$res = true;
		}

		if(tstrlen($cart->getCustomerInformation()->getTelephoneNumber()) < 1){
			$cart->addErrorMessage("tel_number","電話番号を入力して下さい。");
			$res = true;
		}

		//new_password
		if(isset($_POST["new_password"]) && is_array($_POST["new_password"]) &&
			(strlen($_POST["new_password"]["old"]) > 0 || strlen($_POST["new_password"]["new"]) > 0)
		){
			$old = @$_POST["new_password"]["old"];
			$new = @$_POST["new_password"]["new"];
			
			try{
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $cart->getCustomerInformation();
				$user = $userDAO->getById($cart->getAttribute("logined_userid"));
				
				if($user->checkPassword($old) && strlen($new) > 0){
					$cart->setAttribute("new_password",$new);
				}else{
					$cart->addErrorMessage("password_error","パスワードが違います。");
					$res = true;
				}
			}catch(Exception $e){
				
			}
		}

		return $res;
	}
}

?>