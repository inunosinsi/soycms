<?php
class IndexPage extends MobileMyPagePageBase{

	function doPost(){


		if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){

			//パスワードチェック用
			$passwordCheck = true;

			$mypage = MyPageLogic::getMyPage();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			$user = new SOYShop_User();

			//名前関連のデータの文字列変換
			$customer = $_POST["Customer"];
			$customer["name"] = $this->_trim($customer["name"]);
			$customer["reading"] = $this->convertKana($customer["reading"]);	//管理画面の検索対策のため、全角カナに変換
				
			//POSTデータ
			$postUser = (object)$customer;
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($postUser as $key => $value){
					if($key=="birthday"){
						foreach($value as $val){
							$array[$key][]=@mb_convert_encoding($val,"UTF-8","SJIS");
						}
					}else{
						$array[$key] = @mb_convert_encoding($value,"UTF-8","SJIS");
					}

				}
				$postUser = (object)$array;
			}

			$user = SOY2::cast("SOYShop_User",$postUser);

			$mypage->setUserInfo($user);
			$mypage->save();

			if($passwordCheck && !$this->checkError($mypage)){
				$session = false;
				if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
					if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
						$session = true;
					}
				}
				$this->jump("register/confirm",$session);
			}
		}

		//郵便番号での住所検索
		if(isset($_POST["user_zip_search"]) || isset($_POST["user_zip_search_x"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");
			$mypage = MyPageLogic::getMyPage();

			$postUser = (object)$_POST["Customer"];
			if(defined("SOYSHOP_IS_MOBILE")&&defined("SOYSHOP_MOBILE_CHARSET")&&SOYSHOP_MOBILE_CHARSET=="Shift_JIS"){
				$array = array();
				foreach($postUser as $key => $value){
					if($key=="birthday"){
						foreach($value as $val){
							$array[$key][]=@mb_convert_encoding($val,"UTF-8","SJIS");
						}
					}else{
						$array[$key] = @mb_convert_encoding($value,"UTF-8","SJIS");
					}
				}
				$postUser = (object)$array;
			}

			$user = SOY2::cast("SOYShop_User",$postUser);

			$code = soyshop_cart_address_validate($user->getZipcode());
			$res = $logic->search($code);
			$user->setArea(SOYShop_Area::getAreaByText($res["prefecture"]));
			$user->setAddress1($res["address1"]);
			$user->setAddress2($res["address2"]);
			$anchor = "zipcode1";

			$mypage->setUserInfo($user);
			$mypage->save();

		}
	}

	function __construct(){
		parent::__construct();

		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin())$this->jump("order");//すでにログインしていたら飛ばす

		$user = $mypage->getUserInfo();
		if(is_null($user))$user = new SOYShop_User();

		$url = soyshop_get_mypage_url() . "/register";
		if(isset($_GET[session_name()])){
			$url = $url."?".session_name() . "=" . session_id();
		}

		$this->addForm("form", array(
			"method" => "post",
			"action" => $url
		));

		//顧客情報フォーム
		$this->buildForm($user);

		//エラー周り
		DisplayPlugin::toggle("has_error",$mypage->hasError());
		$this->appendErrors($mypage);
	}

	function buildForm($user){

		//メールアドレス
		$this->addInput("user_mail_address", array(
    		"name" => "Customer[mailAddress]",
    		"value" => $user->getMailAddress(),
    	));

		//パスワード
    	$this->addInput("password", array(
    		"name" => "Customer[password]",
    		"value" => $user->getPassword(),
    	));

    	//パスワードのテキスト
    	$this->createAdd("password_text","HTMLLabel", array(
    		"text" => str_repeat("*", tstrlen($user->getPassword()))
    	));

		//氏名
    	$this->addInput("user_name", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));


		//フリガナ
    	$this->addInput("user_furigana", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

		//性別　男
    	$this->addCheckBox("gender_male", array(
    		/**"type" => "radio",**/
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 OR $user->getGender() === "0") ? true : false
    	));

		//性別　女
    	$this->addCheckBox("gender_female", array(
    		/**"type" => "radio",**/
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 OR $user->getGender() === "1") ? true : false
    	));

    	$this->createAdd("gender_text","HTMLLabel", array(
			"text" => ($user->getGender() === 0 || $user->getGender() === "0") ? "男性" :
			        ( ($user->getGender() === 1 || $user->getGender() === "1") ? "女性" : "" )
		));

		/*
		 * 年月日のnameが同じ（Customer[birthday][]）だと
		 * auのケータイブラウザで最後に指定されたvalueが全部に入ってしまうので、
		 * Customer[birthday][0] Customer[birthday][1] Customer[birthday][2]
		 * のように変えておく
		 */
		//生年月日　年
    	$this->addInput("birth_year", array(
    		"name" => "Customer[birthday][0]",
    		"value" => $user->getBirthdayYear(),
    	));

		//生年月日　月
    	$this->addInput("birth_month", array(
    		"name" => "Customer[birthday][1]",
    		"value" => $user->getBirthdayMonth(),
    	));

		//生年月日　日
    	$this->addInput("birth_day", array(
    		"name" => "Customer[birthday][2]",
    		"value" => $user->getBirthdayDay(),
    	));

		//郵便番号
    	$this->addInput("user_post_number", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode()
    	));

		//都道府県
    	$this->createAdd("user_area","HTMLSelect", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $user->getArea()
    	));

    	$this->createAdd("user_area_text","HTMLLabel", array(
			"text" => $user->getAreaText()
		));

		//住所入力1
    	$this->addInput("user_address1", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

		//住所入力2
    	$this->addInput("user_address2", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));

		//電話番号
    	$this->addInput("user_tel_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

		//FAX番号
    	$this->addInput("user_fax_number", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

		//携帯電話番号
    	$this->addInput("user_keitai_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

		//勤務先名称・職種
    	$this->addInput("user_office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));

    	//備考
    	$this->addTextarea("order_memo", array(
    		"name" => "Customer[memo]",
    		"value" => $user->getMemo()
    	));

    	//ログイン画面へ戻る
    	$this->createAdd("return_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/login"
    	));
	}


	/**
	 * エラー周りを設定
	 */
	function appendErrors($mypage){

		$this->createAdd("mail_address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("mail_address")
		));

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

		$this->createAdd("send_address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("send_address")
		));

		$this->createAdd("has_send_address_error","HTMLModel", array(
			"visible" => (strlen($mypage->getErrorMessage("send_address")) > 0)
		));

		$this->createAdd("password_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("password")
		));

	}

	/**
	 * @return boolean
	 */
	function checkError($mypage){

		$res = false;
		$mypage->clearErrorMessage();

		if(tstrlen($mypage->getUserInfo()->getMailAddress()) < 1){
			$mypage->addErrorMessage("mail_address","メールアドレスを入力してください。");
			$res = true;
		}else if(!isValidEmail($mypage->getUserInfo()->getMailAddress())){
			$mypage->addErrorMessage("mail_address","メールアドレスの書式が不正です。");
			$res = true;
		}

		if(tstrlen($mypage->getUserInfo()->getName()) < 1){
			$mypage->addErrorMessage("name","お名前を入力してください。");
			$res = true;
		}

		if(tstrlen($mypage->getUserInfo()->getReading()) < 1){
			$mypage->addErrorMessage("reading","お名前のフリガナを入力してください。");
			$res = true;
		}
		
//		$reading = str_replace(array(" ","　"),"",$mypage->getUserInfo()->getReading());
//		if(!mb_ereg("^[ア-ン゛゜ァ-ォャ-ョー「」、]+$", $reading) && !mb_ereg('^[ｱ-ﾝ]+$', $reading)){
//			$mypage->addErrorMessage("reading","お名前のフリガナをカタカナで入力してください。");
//			$res = true;
//		}

		if(tstrlen($mypage->getUserInfo()->getZipCode()) < 1){
			$mypage->addErrorMessage("ziop_code","郵便番号を入力してください。");
			$res = true;
		}

		if(tstrlen($mypage->getUserInfo()->getArea())<1 || tstrlen($mypage->getUserInfo()->getAddress1()) < 1){
			$mypage->addErrorMessage("address","住所を入力してください。");
			$res = true;
		}

		if(tstrlen($mypage->getUserInfo()->getTelephoneNumber()) < 1){
			$mypage->addErrorMessage("tel_number","電話番号を入力して下さい。");
			$res = true;
		}

		//password
		if(tstrlen($mypage->getUserInfo()->getPassword()) < 1){
			$mypage->addErrorMessage("password","パスワードを入力して下さい。");
			$res = true;
		}

		//@TODO パスワードチェックの強化

		//メールアドレスの重複チェック
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $dao->getByMailAddress($mypage->getUserInfo()->getMailAddress());

			$tmpUser = SOYShop_DataSets::get("config.mypage.tmp_user_register", 1);

			if($tmpUser){
				//仮登録処理を行う
				if($user->getUserType() != SOYShop_User::USERTYPE_TMP){
					$mypage->addErrorMessage("mail_address","既に登録されているメールアドレスです。");
					$res = true;
				}

			}else{
				//仮登録処理を行わない
				$mypage->addErrorMessage("mail_address","既に登録されているメールアドレスです。");
				$res = true;


			}

		}catch(Exception $e){

		}
		return $res;
	}

}
?>