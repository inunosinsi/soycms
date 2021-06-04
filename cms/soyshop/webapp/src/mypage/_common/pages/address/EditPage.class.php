<?php
class EditPage extends MainMyPagePageBase{

	protected $address_key;

	function doPost(){

		$mypage = $this->getMyPage();

		//保存
		if(soy2_check_token() && soy2_check_referer()){
			if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){

				/*
				 * 宛先の入力値
				 */
				$address = $_POST["Address"];

				//名前のデータの整形
				$address["name"] = $this->_trim($address["name"]);
				$address["reading"] = $this->convertKana($address["reading"]);

				//セッションに保存
				$mypage->setAttribute("address", $address);

				//エラーがなければ確認へ
				if(!self::checkError($address)){
					$this->jump("address/confirm/" . $this->address_key);
				}else{
					$this->jump("address/edit/" . $this->address_key);
				}
			}
		}

		//郵便番号での住所検索
		if(isset($_POST["send_zip_search"]) || isset($_POST["send_zip_search"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");

			//宛先の入力値
			$address = $_POST["Address"];

			//検索
			$code = soyshop_cart_address_validate($address["zipCode"]);
			$res = $logic->search($code);
			$address["area"] = SOYShop_Area::getAreaByText($res["prefecture"]);
			$address["address1"] = $res["address1"];
			$address["address2"] = $res["address2"];
			$anchor = "zipcode";

			$mypage->setAttribute("address", $address);

			$mypage->save();

			//編集画面に戻る
			$this->jump("address/edit/" . $this->address_key);
		}
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

		$this->address_key = isset($args[0]) ? $args[0] : 0 ;
		parent::__construct();

		//セッションの値があればそれを使う
		$address = $this->getMyPage()->getAttribute("address");
		if(!$address){
			//保存されている値
			//keyがなければ自動的に新規アドレスになる
			$address = $this->getUser()->getAddress($this->address_key);
		}

		self::buildSendForm($address);

		$mypage = $this->getMyPage();
		DisplayPlugin::toggle("has_error", $mypage->hasError());
		self::appendErrors($mypage);

		$this->addLink("address_link", array(
			"link" => soyshop_get_mypage_url() . "/address"
		));

	}

	private function buildSendForm($address){

		$this->addForm("form");

		//法人名(勤務先)
    	$this->addInput("send_office", array(
    		"name" => "Address[office]",
    		"value" => (isset($address["office"])) ? $address["office"] : "",
    	));

		//氏名
		$this->addInput("send_name", array(
    		"name" => "Address[name]",
    		"value" => (isset($address["name"])) ? $address["name"] : "",
    	));

		//フリガナ
    	$this->addInput("send_reading", array(
    		"name" => "Address[reading]",
    		"value" => (isset($address["reading"])) ? $address["reading"] : "",
    	));

		//郵便番号
    	$this->addInput("send_zip_code", array(
    		"name" => "Address[zipCode]",
    		"value" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
    	));

		//都道府県
    	$this->addSelect("send_area", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => (isset($address["area"])) ? $address["area"] : null,
    	));

		//都道府県テキスト
		$this->addLabel("send_area_text", array(
			"text" => (isset($address["area"])) ? SOYShop_Area::getAreaText($address["area"]) : ""
		));

		//住所入力1
    	$this->addInput("send_address1", array(
    		"name" => "Address[address1]",
    		"value" => (isset($address["address1"])) ? $address["address1"] : "",
    	));

		//住所入力2
    	$this->addInput("send_address2", array(
    		"name" => "Address[address2]",
    		"value" => (isset($address["address2"])) ? $address["address2"] : "",
    	));

		$this->addInput("send_address3", array(
    		"name" => "Address[address3]",
    		"value" => (isset($address["address3"])) ? $address["address3"] : "",
    	));

		//電話番号
    	$this->addInput("send_tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

	}

	/**
	 * エラー周りを設定
	 */
	private function appendErrors(MyPageLogic $mypage){

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

	private function checkError($address){
		$res = false;
		$mypage = $this->getMyPage();
		$mypage->clearErrorMessage();

		if(tstrlen($address["name"]) < 1){
			$mypage->addErrorMessage("name", MessageManager::get("USER_NAME_EMPTY"));
			$res = true;
		}

		$reading = str_replace(array(" ","　"), "", $address["reading"]);
		if(tstrlen($reading) < 1){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_EMPTY"));
			$res = true;
		}

		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])", "", $reading)) !== 0){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_FALSE"));
			$res = true;
		}

		if(tstrlen($address["zipCode"]) < 1){
			$mypage->addErrorMessage("zip_code", MessageManager::get("ZIP_CODE_EMPTY"));
			$res = true;
		}

		if(tstrlen($address["area"]) < 1 || tstrlen($address["address1"] . $address["address2"]) < 1){
			$mypage->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
			$res = true;
		}

		if(tstrlen($address["telephoneNumber"]) < 1){
			$mypage->addErrorMessage("tel_number", MessageManager::get("TELEPHONE_NUMBER_EMPTY"));
			$res = true;
		}

		$mypage->save();

		return $res;
	}
}
