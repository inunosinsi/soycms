<?php
class LoginPage extends MobileMyPagePageBase{

	function doPost(){

		if(isset($_POST["login"]) || isset($_POST["login_x"])){
			if(isset($_POST["mail"]) && isset($_POST["password"])){

				if($this->login(trim($_POST["mail"]),trim($_POST["password"]))){
					//auto login
					if(isset($_POST["login_memory"]))$this->autoLogin();

					$session = false;
					if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
						if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
							$session = true;
						}
					}

					$this->jumpToTop($session);
				}

			}
		}

	}

	function __construct(){


		WebPage::__construct();
		//ログイン済み
		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin())$this->jumpToTop();

		$this->createAdd("login_form","HTMLForm");

		$this->createAdd("login_mail","HTMLInput", array(
			"name" => "mail",
			"value" => @$_POST["mail"]
		));

		$this->createAdd("login_password","HTMLInput", array(
			"name" => "password",
			"value" => @$_POST["password"]
		));

		$this->createAdd("auto_login","HTMLCheckBox", array(
			"name" => "login_memory",
			"elementId" => "login_memory"
		));

		$this->createAdd("remind_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));

		$this->createAdd("register_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));

		$this->createAdd("return_link","HTMLLink", array(
			"link" => soyshop_get_site_url()
		));

		//エラー周り
		DisplayPlugin::toggle("has_error",strlen($mypage->getErrorMessage("login_error")));
		$null = null;

		$mypage->clearErrorMessage();
		$mypage->save();

	}

	/**
	 * ログイン
	 */
	function login($userId,$password){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->login($userId,$password);
	}

	/**
	 * 自動ログイン
	 */
	function autoLogin(){
		$mypage = MyPageLogic::getMyPage();
		$mypage->autoLogin();
	}
}
?>