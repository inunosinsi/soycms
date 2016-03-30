<?php
class EditPage extends MobileMyPagePageBase{

	function doPost(){

		if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){

			$mypage = MyPageLogic::getMyPage();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			$user = new SOYShop_User();

			/*
			 * 宛先
			 */
			$address = $_POST["Address"];

			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($address as $key => $value){
					$array[$key] = @mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$address = $array;
			}

			$mypage->setAttribute("address",$address);

			if(!$this->checkError($address)){
				$this->jump("address/confirm/" . $this->address_key);
			}

		}

		//郵便番号での住所検索
		if(isset($_POST["send_zip_search"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");
			$mypage = MyPageLogic::getMyPage();

			//宛先
			$address = $_POST["Address"];

			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($address as $key => $value){
					$array[$key] = @mb_convert_encoding($value,"UTF-8","SJIS");
				}
				$address = $array;
			}

			$code = soyshop_cart_address_validate($address["zipCode"]);
			$res = $logic->search($code);
			$address["area"] = SOYShop_Area::getAreaByText($res["prefecture"]);
			$address["address1"] = $res["address1"];
			$address["address2"] = $res["address2"];

			$mypage->setAttribute("address",$address);
		}
	}

	function EditPage($args){

		$this->address_key = isset($args[0]) ? $args[0] : 0 ;

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす

		WebPage::WebPage();

		//セッションの値があればそれを使う
		$address = $mypage->getAttribute("address");
		if(!$address){
			//保存されている値
			//keyがなければ自動的に新規アドレスになる
			$address = $this->getUser()->getAddress($this->address_key);
		}

		$this->buildSendForm($address);

		$url = soyshop_get_mypage_url() . "/address/edit/" . $this->address_key;
		if(isset($_GET[session_name()])){
			$url = $url."?".session_name() . "=" . session_id();
		}
		$this->addForm("form", array(
			"method" => "post",
			"action" => $url
		));

		$this->createAdd("return_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));

		DisplayPlugin::toggle("has_error",$mypage->hasError());
		$this->appendErrors($mypage);

	}

	protected $address_key;

	function buildSendForm($address){

		//法人名(勤務先)
    	$this->addInput("send_office", array(
    		"name" => "Address[office]",
    		"value" => $address["office"],
    	));

		//氏名
		$this->addInput("send_name", array(
    		"name" => "Address[name]",
    		"value" => $address["name"],
    	));

		//フリガナ
    	$this->addInput("send_reading", array(
    		"name" => "Address[reading]",
    		"value" => $address["reading"],
    	));

		//郵便番号
    	$this->addInput("send_zip_code", array(
    		"name" => "Address[zipCode]",
    		"value" => $address["zipCode"],
    	));

		//都道府県
    	$this->createAdd("send_area","HTMLSelect", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $address["area"],
    	));

		//都道府県テキスト
		$this->createAdd("send_area_text","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($address["area"])
		));

		//住所入力1
    	$this->addInput("send_address1", array(
    		"name" => "Address[address1]",
    		"value" => $address["address1"],
    	));

		//住所入力2
    	$this->addInput("send_address2", array(
    		"name" => "Address[address2]",
    		"value" => $address["address2"],
    	));

		//電話番号
    	$this->addInput("send_tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => $address["telephoneNumber"],
    	));

	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors($mypage){


		$this->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("name")
		));

		$this->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("reading")
		));

		$this->createAdd("zip_code_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("zip_code")
		));

		$this->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("address")
		));

		$this->createAdd("tel_number_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("tel_number")
		));

	}



	function checkError($address){
		$res = false;
		$mypage = MyPageLogic::getMyPage();
		$mypage->clearErrorMessage();

		if(tstrlen($address["name"]) < 1){
			$mypage->addErrorMessage("name","お名前を入力してください。");
			$res = true;
		}

		if(tstrlen($address["reading"]) < 1){
			$mypage->addErrorMessage("reading","お名前のフリガナを入力してください。");
			$res = true;
		}

		if(tstrlen($address["zipCode"]) < 1){
			$mypage->addErrorMessage("zip_code","郵便番号を入力してください。");
			$res = true;
		}

		if(tstrlen($address["area"])<1 || tstrlen($address["address1"].$address["address2"]) < 1){
			$mypage->addErrorMessage("address","住所を入力してください。");
			$res = true;
		}

		if(tstrlen($address["telephoneNumber"]) < 1){
			$mypage->addErrorMessage("tel_number","電話番号を入力して下さい。");
			$res = true;
		}

		return $res;
	}




	function getListId() {
		return $this->listId;
	}
	function setListId($listId) {
		$this->listId = $listId;
	}

	function getAddress() {
		return $this->address;
	}
	function setAddress($address) {
		$this->address = $address;
	}

	function getAddress_key() {
		return $this->address_key;
	}
	function setAddress_key($address_key) {
		$this->address_key = $address_key;
	}
}
?>