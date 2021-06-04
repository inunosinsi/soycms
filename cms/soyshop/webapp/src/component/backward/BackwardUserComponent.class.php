<?php

/**
 * カート・マイページ ユーザ項目 後方互換性確保
 */
class BackwardUserComponent {

	/**
	 * 後方互換性 カートのユーザ登録
	 * @param MainCartPageBase $page ページクラス
	 * @param SOYShop_User $user
	 */
	public function backwardCartRegister(MainCartPageBase $page, SOYShop_User $user){

		$cart = CartLogic::getCart();

		//メールアドレス
		$page->addInput("mail_address", array(
    		"name" => "Customer[mailAddress]",
    		"value" => $user->getMailAddress(),
			"readonly" => ($cart->getAttribute("logined"))
    	));

		$mypage = MyPageLogic::getMyPage();

    	$page->addModel("password_input", array(
    		"visible" => (!$cart->getAttribute("logined") && !$mypage->getIsLoggedin()),
    	));

    	$page->addModel("new_password", array(
    		"visible" => ($cart->getAttribute("logined") || $mypage->getIsLoggedin()),
    	));

		//パスワード
    	$page->addInput("password", array(
    		"name" => "Customer[password]",
    		"value" => $user->getPassword(),
    	));

		//氏名
    	$page->addInput("name", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));

		//フリガナ
    	$page->addInput("furigana", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

		//性別 男
    	$page->addCheckBox("gender_male", array(
    		"type" => "radio",
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 || $user->getGender() === "0")
    	));

		//性別 女
    	$page->addCheckBox("gender_female", array(
    		"type" => "radio",
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 OR $user->getGender() === "1")
    	));

		//生年月日 年
    	$page->addInput("birth_year", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayYear(),
			"size" => 5,
			"attr:min" => 1900,
			"attr:max" => date("Y") + 10
    	));

		//生年月日 月
    	$page->addInput("birth_month", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayMonth(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 12
    	));

		//生年月日 日
    	$page->addInput("birth_day", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayDay(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 31
    	));

		//郵便番号
    	$page->addInput("post_number", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode()
    	));

		//都道府県
    	$page->addSelect("area", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => (!is_null($user->getArea())) ? $user->getArea() : SOYShop_ShopConfig::load()->getDefaultArea()
    	));

		//住所入力1
    	$page->addInput("address1", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

		//住所入力2
    	$page->addInput("address2", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));

		//住所入力2
    	$page->addInput("address3", array(
    		"name" => "Customer[address3]",
    		"value" => $user->getAddress3(),
    	));

		//電話番号
    	$page->addInput("tel_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

		//FAX番号
    	$page->addInput("fax_number", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

		//携帯電話番号
    	$page->addInput("ketai_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

		//勤務先名称・職種
    	$page->addInput("office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));

	}

	/**
	 * 後方互換性 カートのユーザ登録 確認画面
	 * @param MainCartPageBase $page ページクラス
	 * @param SOYShop_User $user
	 */
	public function backwardCartConfirm(MainCartPageBase $page, SOYShop_User $user){

	}

	/**
	 * 後方互換性 カート 登録 エラーメッセージ
	 * @param MainCartPageBase $page
	 * @param CartLogic $cart
	 */
	public function backwardCartAppendErrors(MainCartPageBase $page, CartLogic $cart){
		//メールアドレス
		$page->createAdd("mail_address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("mail_address")
		));

		//名前
		$page->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("name")
		));

		//フリガナ
		$page->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("reading")
		));

		//郵便番号
		$page->createAdd("zip_code_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("zip_code")
		));

		//住所
		$page->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("address")
		));

		//電話番号
		$page->createAdd("tel_number_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("tel_number")
		));

		//送り先
		$page->createAdd("send_address_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("send_address")
		));

		//送り先 表示
		$page->createAdd("has_send_address_error","HTMLModel", array(
			"visible" => (strlen($cart->getErrorMessage("send_address")) > 0)
		));

		//パスワード
		$page->createAdd("password_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("password_error")
		));

		//パスワード 間違え
		$page->createAdd("password_invalid", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("password_error")
		));

	}

	/**
	 * 後方互換性 カートのユーザ登録 エラーチェック
	 * エラーがなければtrue
	 * @param SOYShop_User $user
	 * @param CartLogic $cart
	 * @return boolean
	 */
	public function backwardCartRegisterCheck(SOYShop_User $user, CartLogic $cart){
		$res = true;

		if(tstrlen($cart->getCustomerInformation()->getMailAddress()) < 1){
			$cart->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_EMPTY"));
			$res = false;
		}else if(!isValidEmail($cart->getCustomerInformation()->getMailAddress())){
			$cart->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_FALSE"));
			$res = false;
		}

		if(tstrlen($cart->getCustomerInformation()->getName()) < 1){
			$cart->addErrorMessage("name", MessageManager::get("USER_NAME_EMPTY"));
			$res = false;
		}

		$reading = str_replace(array(" ","　"), "", $cart->getCustomerInformation()->getReading());
		if(tstrlen($reading) < 1){
			$cart->addErrorMessage("reading", MessageManager::get("USER_READING_EMPTY"));
			$res = false;
		}

		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])", "", $reading)) !== 0){
			$cart->addErrorMessage("reading", MessageManager::get("USER_READING_FALSE"));
			$res = false;
		}

		if(tstrlen($cart->getCustomerInformation()->getZipCode()) < 1){
			$cart->addErrorMessage("zip_code", MessageManager::get("ZIP_CODE_EMPTY"));
			$res = false;
		}

		if(tstrlen($cart->getCustomerInformation()->getArea()) < 1 || tstrlen($cart->getCustomerInformation()->getAddress1()) < 1){
			$cart->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
			$res = false;
		}

		if(tstrlen($cart->getCustomerInformation()->getTelephoneNumber()) < 1){
			$cart->addErrorMessage("tel_number", MessageManager::get("TELEPHONE_NUMBER_EMPTY"));
			$res = false;
		}

		SOY2::import("domain.config.SOYShop_ShopConfig");
		$passCnt = SOYShop_ShopConfig::load()->getPasswordCount();

		//パスワード
		if( $cart->getAttribute("logined") ){
			//ログイン時：パスワード変更
			if(isset($_POST["new_password"]) && is_array($_POST["new_password"]) &&
				(strlen($_POST["new_password"]["old"]) > 0 || strlen($_POST["new_password"]["new"]) > 0)
			){
				$old = (isset($_POST["new_password"]["old"])) ? $_POST["new_password"]["old"] : "";
				$new = (isset($_POST["new_password"]["new"])) ? $_POST["new_password"]["new"] : "";

				try{
					$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
					$user = $cart->getCustomerInformation();
					$user = $userDAO->getById($cart->getAttribute("logined_userid"));

					if( $user->checkPassword($old) ){
						if( strlen($new) < $passCnt ){
							$cart->addErrorMessage("password_error", MessageManager::get("NEW_PASSWORD_COUNT_NOT_ENOUGH", array("password_count" => $passCnt)));
							$res = false;
						}else{
							$cart->setAttribute("new_password", $new);
						}
					}else{
						$cart->addErrorMessage("password_error", MessageManager::get("OLD_PASSWORD_DIFFERENT"));
						$res = false;
					}
				}catch(Exception $e){
					//DB error?
				}
			}
		}else{
			//未ログイン時
			if( tstrlen($cart->getCustomerInformation()->getPassword()) ){
				if(tstrlen($cart->getCustomerInformation()->getPassword()) < $passCnt){
					$cart->addErrorMessage("password_error", MessageManager::get("PASSWORD_COUNT_NOT_ENOUGH", array("password_count" => $passCnt)));
					$res = false;
				}
			}
		}

		return $res;
	}

	/**
	 * 後方互換性 マイページのユーザ登録
	 * @param MainMyPagePageBase $page
	 * @param SOYShop_User $user
	 */
	public function backwardMyPageRegister(MainMyPagePageBase $page, SOYShop_User $user){
		/* buildForm */

		$page->addForm("form");

		//メールアドレス
		$page->addInput("user_mail_address", array(
    		"name" => "Customer[mailAddress]",
    		"value" => $user->getMailAddress(),
    	));

    	//ログインID
    	$page->addInput("user_account_id", array(
    		"name" => "Customer[accountId]",
    		"value" => $user->getAccountId(),
    		"style" => "ime-mode:inactive;"
    	));

		//パスワード
    	$page->addInput("password", array(
    		"name" => "Customer[password]",
    		"value" => $user->getPassword(),
    	));

    	//パスワードのテキスト
    	$page->addLabel("password_text", array(
    		"text" => tstrlen($user->getPassword()) ? str_repeat("*", tstrlen($user->getPassword())) . MessageManager::get("NO_DISPLAY_PASSWORD_CHANGE") : MessageManager::get("NO_CHANGE"),
    	));

		//氏名
    	$page->addInput("user_name", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));

		//フリガナ
    	$page->addInput("user_furigana", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

    	//フリガナ
    	$page->addInput("user_reading", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

    	//ニックネーム
    	$page->addInput("user_nickname", array(
    		"name" => "Customer[nickname]",
    		"value" => $user->getNickname(),
    	));

    	//URL
    	$page->addInput("user_url", array(
    		"name" => "Customer[url]",
    		"value" => $user->getUrl(),
    	));

		//性別　男
    	$page->addCheckBox("gender_male", array(
    		/**"type" => "radio",**/
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 || $user->getGender() === "0"),//nullはfalse
    	));

		//性別　女
    	$page->addCheckBox("gender_female", array(
    		/**"type" => "radio",**/
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 || $user->getGender() === "1"),
    	));

    	//性別 テキスト
    	$page->addLabel("gender_text", array(
			"text" => ($user->getGender() === 0 || $user->getGender() === "0") ? MessageManager::get("SEX_MALE") :
			        ( ($user->getGender() === 1 || $user->getGender() === "1") ? MessageManager::get("SEX_FEMALE") : "" )
		));

		//生年月日　年
    	$page->addInput("birth_year", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayYear(),
			"size" => 5,
			"attr:min" => 1900,
			"attr:max" => date("Y") + 10
    	));

		//生年月日　月
    	$page->addInput("birth_month", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayMonth(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 12
    	));

		//生年月日　日
    	$page->addInput("birth_day", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayDay(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 31
    	));

		//郵便番号
    	$page->addInput("user_post_number", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode(),
    	));

    	$page->addInput("user_zip_code", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode(),
    	));

		//都道府県
    	$page->addSelect("user_area", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => (!is_null($user->getArea())) ? $user->getArea() : SOYShop_ShopConfig::load()->getDefaultArea(),
    	));

    	$page->addLabel("user_area_text", array(
			"text" => $user->getAreaText()
		));

		//住所入力1
    	$page->addInput("user_address1", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

		//住所入力2
    	$page->addInput("user_address2", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));
		$page->addInput("user_address3", array(
    		"name" => "Customer[address3]",
    		"value" => $user->getAddress3(),
    	));

		//電話番号
    	$page->addInput("user_tel_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));
    	$page->addInput("user_telephone_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

		//FAX番号
    	$page->addInput("user_fax_number", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

		//携帯電話番号
    	$page->addInput("user_keitai_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));
    	$page->addInput("user_cellphone_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

		//勤務先名称・職種
    	$page->addInput("user_office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));

    	//勤務先名称・職種
    	$page->addInput("user_job_name", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));

    	//備考
    	$page->addTextarea("order_memo", array(
    		"name" => "Customer[memo]",
    		"value" => $user->getMemo()
    	));

	}

	/**
	 * 後方互換性 マイページのユーザ情報編集
	 * @param MainMyPagePageBase $page
	 * @param SOYShop_User $user
	 */
	public function backwardMyPageEdit(MainMyPagePageBase $page, SOYShop_User $user){
		$mypage = MyPageLogic::getMyPage();

		$page->addForm("form", array(
			"enctype" => "multipart/form-data"
		));

		//LINE Loginで生成したダミーの場合はメールアドレスを空にする
		$mailAddress = $user->getMailAddress();
		if(preg_match('/^line_.*@' . DUMMY_MAIL_ADDRESS_DOMAIN . '$/', $mailAddress, $tmp)){
			if(isset($tmp[0]) && strlen($tmp[0]) && strpos($tmp[0], "@")){
				$mailAddress = "";
			}
		}
		$page->addInput("user_mail_address", array(
    		"name" => "Customer[mailAddress]",
    		"value" => $mailAddress,
    	));

    	//メールアドレス
		$page->addInput("user_account_id", array(
    		"name" => "Customer[accountId]",
    		"value" => $user->getAccountId(),
    	));

		//氏名
    	$page->addInput("user_name", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));

		//フリガナ
    	$page->addInput("user_furigana", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));
    	$page->addInput("user_reading", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));

    	//ニックネーム
    	$page->addInput("user_nickname", array(
    		"name" => "Customer[nickname]",
    		"value" => $user->getNickname()
    	));

    	//プロフィール
    	$page->addCheckBox("profile_display", array(
    		"name" => "Customer[isProfileDisplay]",
    		"value" => SOYShop_User::PROFILE_IS_DISPLAY,
    		"selected" => ($user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY),
    		"label" => MessageManager::get("DISPLAY_PROFILE_PAGE")
    	));

    	$page->addLabel("profile_display_text", array(
    		"text" => ($user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY) ? MessageManager::get("DISPLAY_PROFILE_PAGE") : MessageManager::get("NO_DISPLAY_PROFILE_PAGE")
    	));

		$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");
		$width = $userLogic->getDisplayImage($user);
    	$page->addImage("user_image", array(
    		"src" => $user->getAttachmentsUrl() . $user->getImagePath(),
    		"visible" => (strlen($user->getImagePath()) > 0),
    		"style" => "width:" . $width . "px;"
    	));

    	$page->addInput("user_image_path", array(
    		"name" => "Customer[imagePath]",
    		"value" => $user->getImagePath(),
    		"visible" => (strlen($user->getImagePath()) > 0)
    	));

    	$page->addModel("is_user_image", array(
    		"visible" => (strlen($user->getImagePath()) > 0)
    	));

		$isDeleteImage = $mypage->getAttribute("user.edit.delete_image");
    	$page->addCheckBox("delete_image", array(
    		"name" => "Delete",
    		"value" => 1,
    		"selected" => (isset($isDeleteImage) && $isDeleteImage === true),
    		"label" => MessageManager::get("DELETE_PROFILE_IMAGE")
    	));

    	$page->addModel("confirm_detele_image", array(
    		"visible" => (isset($isDeleteImage) && $isDeleteImage === true)
    	));

    	//URL
    	$page->addInput("user_url", array(
    		"name" => "Customer[url]",
    		"value" => $user->getUrl()
    	));

		//性別　男
    	$page->addCheckBox("gender_male", array(
    		/**"type" => "radio",**/
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 || $user->getGender() === "0") ? true : false
    	));

		//性別　女
    	$page->addCheckBox("gender_female", array(
    		/**"type" => "radio",**/
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 || $user->getGender() === "1") ? true : false
    	));

    	$page->addLabel("gender_text", array(
			"text" => ($user->getGender() === 0 || $user->getGender() === "0") ? MessageManager::get("SEX_MALE") :
			        ( ($user->getGender() === 1 || $user->getGender() === "1") ? MessageManager::get("SEX_FEMALE") : "" )
		));

		//生年月日　年
    	$page->addInput("birth_year", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayYear(),
			"size" => 5,
			"attr:min" => 1900,
			"attr:max" => date("Y") + 10
    	));

		//生年月日　月
    	$page->addInput("birth_month", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayMonth(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 12
    	));

		//生年月日　日
    	$page->addInput("birth_day", array(
			"type" => "number",
    		"name" => "Customer[birthday][]",
    		"value" => $user->getBirthdayDay(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 31
    	));

		//郵便番号
    	$page->addInput("user_post_number", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode()
    	));
    	$page->addInput("user_zip_code", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode()
    	));

		//都道府県
    	$page->addSelect("user_area", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $user->getArea()
    	));

		//都道府県
    	$page->addSelect("array_area", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getArrayAreas(),
    		"value" => SOYShop_Area::getAreaText($user->getArea())
    	));


    	$page->addLabel("user_area_text", array(
    		"text" => $user->getAreaText()
    	));

		//住所入力1
    	$page->addInput("user_address1", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

		//住所入力2
    	$page->addInput("user_address2", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));
		$page->addInput("user_address3", array(
    		"name" => "Customer[address3]",
    		"value" => $user->getAddress3(),
    	));

		//電話番号
    	$page->addInput("user_tel_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));
    	$page->addInput("user_telephone_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

		//FAX番号
    	$page->addInput("user_fax_number", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

		//携帯電話番号
    	$page->addInput("user_keitai_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));
    	$page->addInput("user_cellphone_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

		//勤務先名称・職種
    	$page->addInput("user_office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));
    	$page->addInput("user_job_name", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    	));

    	//備考
    	$page->addTextarea("order_memo", array(
    		"name" => "Customer[memo]",
    		"value" => $user->getMemo()
    	));

	}

	/**
	 * 後方互換性 マイページ ユーザ登録 エラーチェック
	 * エラーがなければtrue
	 * @param SOYShop_User $user
	 * @param MyPageLogic $mypage
	 * @return boolean
	 */
	public function backwardMyPageRegisterCheck(SOYShop_User $user, MyPageLogic $mypage){
		$res = true;

		//メールアドレス
		if(tstrlen($user->getMailAddress()) < 1){
			$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_EMPTY"));
			$res = false;
		}else if(!isValidEmail($user->getMailAddress())){
			$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_FALSE"));
			$res = false;
		}

		//名前
		if(tstrlen($user->getName()) < 1){
			$mypage->addErrorMessage("name", MessageManager::get("USER_NAME_EMPTY"));
			$res = false;
		}

		//フリガナ 入力
		$reading = str_replace(array(" ", "　"), "", $user->getReading());
		if(tstrlen($reading) < 1){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_EMPTY"));
			$res = false;
		}

		//フリガナ フォーマット
		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])","",$reading)) !== 0){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_FALSE"));
			$res = false;
		}

		//郵便番号
		if(tstrlen($user->getZipCode()) < 1){
			$mypage->addErrorMessage("zip_code", MessageManager::get("ZIP_CODE_EMPTY"));
			$res = false;
		}

		//住所
		if(tstrlen($user->getArea()) < 1 || tstrlen($user->getAddress1()) < 1){
			$mypage->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
			$res = false;
		}

		//電話番号
		if(tstrlen($user->getTelephoneNumber()) < 1){
			$mypage->addErrorMessage("tel_number", MessageManager::get("TELEPHONE_NUMBER_EMPTY"));
			$res = false;
		}

		//パスワード
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$passCnt = SOYShop_ShopConfig::load()->getPasswordCount();

		if(tstrlen($user->getPassword()) < 1){
			$mypage->addErrorMessage("password", MessageManager::get("PASSWORD_EMPTY"));
			$res = false;
		}elseif(tstrlen($user->getPassword()) < $passCnt){
			$mypage->addErrorMessage("password", MessageManager::get("PASSWORD_COUNT_NOT_ENOUGH", $passCnt));
			$res = false;
		}elseif(!preg_match("/^[a-zA-Z0-9]+$/",$user->getPassword())){
    		$mypage->addErrorMessage("password", MessageManager::get("PASSWORD_FALSE"));
    	}



		//メールアドレスの重複チェック
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$oldUser = $dao->getByMailAddress($user->getMailAddress());
			$tmpUser = SOYShop_DataSets::get("config.mypage.tmp_user_register", 1);

			//仮登録ユーザだった場合は上書き
			if($tmpUser){
				//仮登録処理を行う
				if($oldUser->getUserType() != SOYShop_User::USERTYPE_TMP){
					$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_REGISTERED_ALREADY"));
					$res = false;
				}

			}else{
				//仮登録処理を行わない
				$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_REGISTERED_ALREADY"));
				$res = false;
			}

		}catch(Exception $e){

		}

		$mypage->save();

		return $res;
	}

	/**
	 * 後方互換性 カートのユーザ編集 エラーチェック
	 * エラーがなければtrue
	 * @param SOYShop_User $user
	 * @param MyPageLogic $mypage
	 * @return boolean
	 */
	public function backwardMyPageEditCheck(SOYShop_User $user, MyPageLogic $mypage){
		$res = true;

		/* メールアドレス */
		if(tstrlen($user->getMailAddress()) < 1){
			$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_EMPTY"));
			$res = false;
		}else if(!isValidEmail($user->getMailAddress())){
			$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_FALSE"));
			$res = false;
		}

		/* 名前 */
		if(tstrlen($user->getName()) < 1){
			$mypage->addErrorMessage("name", MessageManager::get("USER_NAME_EMPTY"));
			$res = false;
		}

		/* フリガナ */
		$reading = str_replace(array(" ","　"), "", $user->getReading());
		if(tstrlen($reading) < 1){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_EMPTY"));
			$res = false;
		}

		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])", "", $reading)) !== 0){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_FALSE"));
			$res = false;
		}

		/* 郵便番号 */
		if(tstrlen($user->getZipCode()) < 1){
			$mypage->addErrorMessage("zip_code", MessageManager::get("ZIP_CODE_EMPTY"));
			$res = false;
		}

		/* 住所 */
		if(tstrlen($user->getArea()) < 1 || tstrlen($user->getAddress1()) < 1){
			$mypage->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
			$res = false;
		}

		/* 電話番号 */
		if(tstrlen($user->getTelephoneNumber()) < 1){
			$mypage->addErrorMessage("tel_number", MessageManager::get("TELEPHONE_NUMBER_EMPTY"));
			$res = false;
		}

		/* メールアドレスの重複チェック */

		//登録されているメールアドレス
		$oldAddress = $mypage->getUser()->getMailAddress();

		//今回入力したメールアドレス
		$newAddress = $user->getMailAddress();

		//すでに登録されているアドレスと入力したアドレスが異なる場合は重複チェックを開始する
		if($oldAddress != $newAddress){
			$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			try{
				$duplication = $userDao->getByMailAddress($newAddress);
				$mypage->addErrorMessage("mail_address", MessageManager::get("MAIL_ADDRESS_REGISTERED_ALREADY"));
				$res = false;
			}catch(Exception $e){
				//問題なし
			}
		}

		/* ユーザカスタムフィールド */
		//各項目をチェック
		SOYShopPlugin::load("soyshop.user.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "checkError",
			"app" => $mypage,
			"param" => $_POST["user_customfield"],
			"user" => $user
		));

		if($delegate->hasError()){
			$mypage->addErrorMessage("customfield", MessageManager::get("CUSTOMFIELD_ERROR"));
			$res = true;
		}else{
			$mypage->removeErrorMessage("customfield");
		}

		return $res;

	}

	/**
	 * 後方互換性 マイページ 登録 エラーメッセージ
	 * @param MainMyPagePageBase $page
	 * @param MyPageLogic $mypage
	 */
	public function backwardMyPageRegisterAppendErrors(MainMyPagePageBase $page, MyPageLogic $mypage){
		//メールアドレス
		$page->createAdd("mail_address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("mail_address")
		));

		//名前
		$page->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("name")
		));

		//フリガナ
		$page->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("reading")
		));

		//郵便番号
		$page->createAdd("zip_code_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("zip_code")
		));

		//住所
		$page->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("address")
		));

		//電話番号
		$page->createAdd("tel_number_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("tel_number")
		));

		//送り先
		$page->createAdd("send_address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("send_address")
		));

		//送り先 表示
		$page->createAdd("has_send_address_error","HTMLModel", array(
			"visible" => (strlen($mypage->getErrorMessage("send_address")) > 0)
		));

		//パスワード
		$page->createAdd("password_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("password")
		));

	}

	/**
	 * 後方互換性 マイページ 編集 エラーメッセージ
	 * @param MainMyPagePageBase $page
	 * @param MyPageLogic $mypage
	 */
	public function backwardMyPageEditAppendErrors(MainMyPagePageBase $page, MyPageLogic $mypage){

		//メールアドレス
		$page->createAdd("mail_address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("mail_address")
		));

		//名前
		$page->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("name")
		));

		//フリガナ
		$page->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("reading")
		));

		//郵便番号
		$page->createAdd("zip_code_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("zip_code")
		));

		//都道府県
		$page->createAdd("pref_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("pref")
		));

		//住所
		$page->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("address")
		));

		//電話番号
		$page->createAdd("tel_number_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("tel_number")
		));

		//パスワード
		$page->createAdd("password_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("password_error")
		));
	}

	/**
	 * 後方互換性 管理画面
	 * @param WebPage $page
	 * @param SOYShop_User $user
	 */
	public function backwardAdminBuildForm(WebPage $page, SOYShop_User $user){

		//名前
    	$page->addInput("name", array(
    		"name" => "Customer[name]",
    		"value" => $user->getName(),
    	));

		//フリガナ
    	$page->addInput("furigana", array(
    		"name" => "Customer[reading]",
    		"value" => $user->getReading(),
    	));


		//ニックネーム
    	$page->addInput("nickname", array(
    		"name" => "Customer[nickname]",
    		"value" => $user->getNickname(),
    	));

		/* 画像関係 */
		$width = 0;
    	$path = $user->getAttachmentsPath() . $user->getImagePath();
    	$imageExists = is_readable($path) && is_file($path) && strlen($user->getImagePath());
    	if($imageExists){
			$image_size = getimagesize($path);
			$width = ($image_size[0] > 480) ? 480 : $image_size[0];
    	}

		//画像
		$dir = str_replace(SOYSHOP_SITE_DIRECTORY, "", $user->getAttachmentsPath());
    	$page->addImage("image", array(
    		"src" => soyshop_get_site_url(true) . $dir. $user->getImagePath(),
    		"visible" => $imageExists,
    		"style" => $imageExists ? "width:". $width."px;" : ""
    	));

		//画像パス
    	$page->addInput("image_path", array(
    		"name" => "Customer[imagePath]",
    		"value" => $user->getImagePath(),
    		"visible" => $imageExists
    	));

		//画像 表示
    	$page->addModel("is_image", array(
    		"visible" => $imageExists
    	));

		//画像削除チェックボックス
    	$page->addCheckBox("delete_image", array(
    		"name" => "Delete",
    		"value" => 1,
    		"label" => MessageManager::get("DELETE_PROFILE_IMAGE")
    	));


		//性別 男性
    	$page->addCheckBox("gender_male", array(
    		"type" => "radio",
			"name" => "Customer[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 || $user->getGender() === "0")
    	));

		//性別 女性
    	$page->addCheckBox("gender_female", array(
    		"type" => "radio",
    		"name" => "Customer[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 || $user->getGender() === "1")
    	));

		//生年月日 年
		$page->addInput("birth_year", array(
			"type" => "number",
    		"name" => "Customer[birthday][0]",
    		"value" => $user->getBirthdayYear(),
			"size" => 5,
			"attr:min" => 1900,
			"attr:max" => date("Y") + 10
    	));

		//生年月日 月
    	$page->addInput("birth_month", array(
			"type" => "number",
    		"name" => "Customer[birthday][1]",
			"value" => $user->getBirthdayMonth(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 12
    	));

		//生年月日 日
    	$page->addInput("birth_day", array(
			"type" => "number",
    		"name" => "Customer[birthday][2]",
    		"value" => $user->getBirthdayDay(),
			"size" => 3,
			"attr:min" => 1,
			"attr:max" => 31
    	));

		//郵便番号
    	$page->addInput("post_number", array(
    		"name" => "Customer[zipCode]",
    		"value" => $user->getZipCode(),
    	));

		//住所 都道府県
    	$page->addSelect("area", array(
    		"name" => "Customer[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => $user->getArea()
    	));

		//住所入力1
    	$page->addInput("address1", array(
    		"name" => "Customer[address1]",
    		"value" => $user->getAddress1(),
    	));

		//住所入力2
    	$page->addInput("address2", array(
    		"name" => "Customer[address2]",
    		"value" => $user->getAddress2(),
    	));

		//電話番号
    	$page->addInput("tel_number", array(
    		"name" => "Customer[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    	));

		//FAX番号
    	$page->addInput("fax_number", array(
    		"name" => "Customer[faxNumber]",
    		"value" => $user->getFaxNumber(),
    	));

		//携帯電話番号
    	$page->addInput("ketai_number", array(
    		"name" => "Customer[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    	));

    	//ログインID
    	$page->addInput("account_id", array(
    		"name" => "Customer[accountId]",
    		"value"=> $user->getAccountId()
    	));

		//URL
    	$page->addInput("url", array(
    		"name" => "Customer[url]",
    		"value" => $user->getUrl(),
    	));


		//備考
    	$page->addTextArea("memo", array(
    		"name" => "Customer[memo]",
    		"text" => $user->getMemo(),
	   	));

		//属性A
    	$page->addInput("attribute1", array(
    		"name" => "Customer[attribute1]",
    		"value" => $user->getAttribute1(),
    	));

		//属性B
    	$page->addInput("attribute2", array(
    		"name" => "Customer[attribute2]",
    		"value" => $user->getAttribute2(),
    	));

		//属性C
    	$page->addInput("attribute3", array(
    		"name" => "Customer[attribute3]",
    		"value" => $user->getAttribute3(),
    	));

		//メール配信をしない
    	$page->addCheckBox("not_send", array(
    		"name" => "Customer[notSend]",
    		"value" => 1,
    		"selected" => ($user->getNotSend() == 1),
    		"elementId" => "not_send"
    	));

		//SOY Shop連携用フラグ
    	$page->addCheckBox("shop_send", array(
    		"name" => "Customer[isDisabled]",
    		"value" => SOYShop_User::USER_IS_DISABLED,
    		"selected" => $user->getIsDisabled(),
    		"elementId" => "checkbox_send_eshop"
    	));

		//SOY Shop連携用フラグ
    	$page->addInput("shop_send_hidden", array(
    		"type" => "hidden",
			"name" => "Customer[isDisabled]",
    		"value" => 0,
    	));

    	/** プロフィール **/
    	$page->addCheckBox("is_profile_display", array(
    		"name" => "Customer[isProfileDisplay]",
    		"value" => SOYShop_User::PROFILE_IS_DISPLAY,
    		"selected" => ($user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY),
    		"label" => MessageManager::get("PUBLISH_PROFILE_PAGE")
    	));

    	//今はまだreadonly
    	$page->addInput("profile_id", array(
    		"name" => "Customer[profileId]",
    		"value" => $user->getProfileId(),
    		"style" => "ime-mode:inactive;",
    		"readonly" => true
    	));

	}
}
