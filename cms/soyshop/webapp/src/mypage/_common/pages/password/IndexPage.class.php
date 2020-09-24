<?php

class IndexPage extends MainMyPagePageBase{

	private $user;

	function doPost(){

		if(!isset($_POST["password"]) || !isset($_POST["confirm"])){
			$this->jump();
		}

		if(soy2_check_token() && soy2_check_referer() && $this->checkPassword()){

			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			try{
				$this->user->setPassword($this->user->hashPassword($_POST["password"]));
				$this->user->clearAttribute("remind_query");

				$userDAO->update($this->user);
				$this->jump("password/complete");
			}catch(Exception $e){
			}

		}
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

		$mypage = $this->getMyPage();
		$mypage->clearErrorMessage();

		$this->user = $this->getUser();

		SOY2::import("domain.config.SOYShop_ShopConfig");

    	parent::__construct();

		//display error message
		DisplayPlugin::toggle("has_error", $mypage->hasError());
		$this->createAdd("error_list", "MainMyPageErrorList", array(
			"list" => $mypage->getErrorMessages()
		));

		$this->addForm("form");

		$this->addLabel("password_count", array(
			"text" => SOYShop_ShopConfig::load()->getPasswordCount()
		));

		$this->addInput("old", array(
			"name" => "old",
			"value" => (isset($_POST["old"]) && strlen($_POST["old"]) > 0) ? $_POST["old"] : ""
		));

		$this->addInput("password", array(
			"name" => "password",
			"value" => (isset($_POST["password"]) && strlen($_POST["password"]) > 0) ? $_POST["password"] : ""
		));

		$this->addInput("confirm", array(
			"name" => "confirm",
			"value" => (isset($_POST["confirm"]) && strlen($_POST["confirm"]) > 0) ? $_POST["confirm"] : ""
		));
    }

    /**
     * check for POST params
     * @return boolen エラーがない場合true
     */
    private function checkPassword(){
		$mypage = $this->getMyPage();

    	$old = $_POST["old"];
    	$password = $_POST["password"];
    	$confirm = $_POST["confirm"];

		$passCnt = SOYShop_ShopConfig::load()->getPasswordCount();

    	if(!$this->user->checkPassword($old)){
    		$mypage->addErrorMessage("remind_password_no_old", MessageManager::get("OLD_PASSWORD_FALSE"));
    	}elseif(tstrlen($password) === 0){
	    	// no input password
    		$mypage->addErrorMessage("remind_password_no_password", MessageManager::get("PASSWORD_CHANGE_NOT_INPUT"));
    	}elseif(tstrlen($password) < $passCnt){
    		// less password
    		$mypage->addErrorMessage("remind_password_less_password", MessageManager::get("PASSWORD_COUNT_NOT_ENOUGH", array("password_count" => $passCnt)));
    	}elseif(!preg_match("/^[a-zA-Z0-9]+$/", $password)){
    		$mypage->addErrorMessage("remind_password_string", MessageManager::get("PASSWORD_FALSE"));
    	}elseif($password !== $confirm){
	    	// different
    		$mypage->addErrorMessage("remind_password_different", MessageManager::get("PASSWORD_REKEY_FALSE"));
    	}

    	return (!$mypage->hasError());
    }
}
