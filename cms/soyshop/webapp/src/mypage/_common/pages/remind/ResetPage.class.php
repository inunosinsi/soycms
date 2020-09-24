<?php

class ResetPage extends MainMyPagePageBase{

	private $mypage;
	private $user;
	private $query;
	private $mail;

	function doPost(){

		//入力フォームに値がなかった場合
		if(!isset($_POST["password"]) || !isset($_POST["confirm"])){
			return false;
		}

		if(soy2_check_token() && soy2_check_referer() && $this->checkPassword() && $this->checkQuery($this->user)){
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			$this->user->setPassword($this->user->hashPassword($_POST["password"]));
			$this->user->clearAttribute("remind_limit");
			$this->user->clearAttribute("remind_query");

			try{
				$userDAO->update($this->user);
				$this->jump("remind/complete");
			}catch(Exception $e){
				//@ToDo 入力エラーの場合のエラー文言
			}
		}
	}

    function __construct() {
		$this->mypage = $this->getMyPage();
		$this->mypage->clearErrorMessage();

		//ログイン済み
		if($this->mypage->getIsLoggedin()){
			$this->jumpToTop();
		}

		//トークンかメールアドレスがなかった場合はリマインドページに飛ばす
		if(!isset($_GET["q"]) || !isset($_GET["f"])){
			$this->jump("remind/input");
		}

		$this->query = $_GET["q"];
		$this->mail = rawurldecode($_GET["f"]);

		$form_visible = false;

    	$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$this->user = $userDAO->getByMailAddress($this->mail);
			$form_visible = $this->checkQuery($this->user);
		}catch(Exception $e){
			$this->user = new SOYShop_User();
		}

    	parent::__construct();

		SOY2::import("domain.config.SOYShop_ShopConfig");

		//display error message
		DisplayPlugin::toggle("has_error", $this->mypage->hasError());
		$this->createAdd("error_list","MainMyPageErrorList", array(
			"list" => $this->mypage->getErrorMessages()
		));

		//if invalid url is accessed
		DisplayPlugin::toggle("invalid_url", !$form_visible);

		$this->addLink("remind_link", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));

		$this->addLabel("password_count", array(
			"text" => SOYShop_ShopConfig::load()->getPasswordCount()
		));

		$this->addForm("form", array(
			"visible" => $form_visible
		));

		$this->addLabel("address", array(
			"text" => $this->mail
		));

		$this->addInput("password", array(
			"name" => "password",
			"value" => (isset($_POST["password"])) ? $_POST["password"] : ""
		));

		$this->addInput("confirm", array(
			"name" => "confirm",
			"value" => (isset($_POST["confirm"])) ? $_POST["confirm"] : ""
		));
    }

    /**
     * check for GET params
     * エラーがないときにtrue
     * @param object SOYShop_User
     * @return boolean
     */
    function checkQuery(SOYShop_User $user){

    	$limit = $user->getAttribute("remind_limit");
    	$query = $user->getAttribute("remind_query");

    	//time limit
    	if(!is_null($limit) && time() > $limit){
    		$this->mypage->addErrorMessage("remind_limit", MessageManager::get("REMINDER_MAIL_OUTSITE_TERM"));
    	}

    	//query
    	if($query !== $this->query){
    		$this->mypage->addErrorMessage("remind_url", MessageManager::get("REMINDER_MAIL_URL_FALSE"));
    	}

    	return (!$this->mypage->hasError());
    }

    /**
     * check for POST params
     * エラーがなければtrueを返す
     * @return boolean
     */
    function checkPassword(){

    	$password = $_POST["password"];
    	$confirm = $_POST["confirm"];

		$passCnt = SOYShop_ShopConfig::load()->getPasswordCount();

    	if(tstrlen($password) === 0){
	    	// no input password
    		$this->mypage->addErrorMessage("remind_password_no_password", MessageManager::get("PASSWORD_NOT_INPUT"));
    	}elseif(tstrlen($password) < $passCnt){
    		// less password
    		$this->mypage->addErrorMessage("remind_password_less_password", MessageManager::get("PASSWORD_COUNT_NOT_ENOUGH", array("password_count" => $passCnt)));
    	}elseif(!preg_match("/^[a-zA-Z0-9]+$/",$password)){
    		$this->mypage->addErrorMessage("remind_password_string", MessageManager::get("PASSWORD_FALSE"));
    	}elseif($password !== $confirm){
	    	// different
    		$this->mypage->addErrorMessage("remind_password_different", MessageManager::get("PASSWORD_REKEY_FALSE"));
    	}

    	return (!$this->mypage->hasError());
    }
}
