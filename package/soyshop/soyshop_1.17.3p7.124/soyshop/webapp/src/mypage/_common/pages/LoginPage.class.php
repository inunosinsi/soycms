<?php
class LoginPage extends MainMyPagePageBase{

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["login"]) || isset($_POST["login_x"])){
				if((isset($_POST["loginId"]) || isset($_POST["mail"])) && isset($_POST["password"])){
					
					//後方互換分も含み、ログインに使うIDを取得する
					$loginId = (isset($_POST["loginId"])) ? trim($_POST["loginId"]) : null;
					if(is_null($loginId)) $loginId = (isset($_POST["mail"])) ? trim($_POST["mail"]) : null;
	
					if($this->login($loginId, trim($_POST["password"]))){
						//auto login
						if(isset($_POST["login_memory"])) $this->autoLogin();
						
						//リダイレクト
						if(isset($_GET["r"]) && strlen($_GET["r"])){
							$r = $_GET["r"];
						}else{
							$mypage = MyPageLogic::getMyPage();
							$r = $mypage->getAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
							if(isset($r)){
								$mypage->clearAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
							}
						}
						
						//jump
						if(isset($r)){
							$param = soyshop_remove_get_value($r);
							soyshop_redirect_designated_page($param, "login=complete");
							exit;
						}
	
						$this->jumpToTop();
					
					//ログインできなかった時
					}else{
						if(isset($_GET["r"]) && strlen($_GET["r"])){
							$param = soyshop_remove_get_value($_GET["r"]);
							soyshop_redirect_designated_page($param, "login=error");
							exit;
						}
					}
				}
			}
		}
	}

	function __construct(){
		
		WebPage::__construct();

		$mypage = MyPageLogic::getMyPage();
		//ログインチェック
		if($mypage->getIsLoggedin()){
			$this->jumpToTop();
		}
		
		//リダイレクト指定で遷移してきた場合はURIを保存する
		if(isset($_GET["r"])){
			$mypage->setAttribute(MyPageLogic::REGISTER_REDIRECT_KEY, $_GET["r"]);
			$mypage->save();
		}

		$this->addForm("login_form");

		$this->addInput("login_id", array(
			"name" => "loginId",
			"value" => (isset($_POST["loginId"])) ? $_POST["loginId"] : ""
		));
		
		//後方互換
		$this->addInput("login_mail", array(
			"name" => "loginId",
			"value" => (isset($_POST["loginId"])) ? $_POST["loginId"] : ""
		));

		$this->addInput("login_password", array(
			"name" => "password",
			"value" => (isset($_POST["password"])) ? $_POST["password"] : ""
		));

		$this->addCheckBox("auto_login", array(
			"name" => "login_memory",
			"elementId" => "login_memory"
		));

		$this->addLink("remind_link", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));

		$this->addLink("register_link", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));

		//エラー周り
		DisplayPlugin::toggle("has_error", strlen($mypage->getErrorMessage("login_error")));
		
		//エラーメッセージ
		$this->createAdd("login_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("login_error")
		));

		$mypage->clearErrorMessage();
		$mypage->save();

	}

	/**
	 * ログイン
	 */
	function login($userId, $password){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->login($userId, $password);
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