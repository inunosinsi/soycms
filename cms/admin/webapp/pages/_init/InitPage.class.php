<?php

class InitPage extends CMSHTMLPageBase{

	private $_message = null;

	function doPost(){

		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("logic.init.InitializeLogic");

	    	$userId = $_POST['userId'];
	    	$password = $_POST['password'];
	    	$password_confirm = $_POST['password_confirm'];

	    	$this->_message = array();

	    	if(strlen($userId) < 4){
	    		$this->_message["userId"] = CMSMessageManager::get("ADMIN_SUPERUSER_ID_IS_TOO_SHORT");
	    	}elseif(strlen($userId) > 255){
	    		$this->_message["userId"] = CMSMessageManager::get("ADMIN_SUPERUSER_ID_IS_TOO_LONG");
	    	}
	    	if(strlen($password) < 6){
	    		$this->_message["password"] = CMSMessageManager::get("ADMIN_PASSWORD_IS_TOO_SHORT");
	    	}elseif(strlen($password) > 255){
	    		$this->_message["password"] = CMSMessageManager::get("ADMIN_PASSWORD_IS_TOO_LONG");
	    	}
	    	if($password != $password_confirm){
	    		$this->_message["password_confirm"] = CMSMessageManager::get("ADMIN_PASSWORDS_NOT_SAME");
	    	}

	    	if(count($this->_message) > 0){
	    		return;
	    	}

	    	if($logic->initialize($userId, $password)){
	    		SOY2PageController::redirect("./index.php/Redirect?userId=" . $userId);
	    	}

			$this->_message["init"] = "[initialize] Failed to initialize cms";
		}
	}

	function __construct(){
		$loginable = self::checkLoginable();

		//初期管理者が作成済み
		if($loginable && ADMIN_DB_EXISTS && self::hasDefaultUser()){
    		SOY2PageController::redirect("./index.php");
		}

		define("HEAD_TITLE", "初回設定 - " . CMSUtil::getCMSName());
		parent::__construct();

		// $this->createAdd("head" ,"HTMLHead",array(
		// 	"title" => "初回設定 - SOY CMS"
		// ));

		$this->addForm("initform");
		$this->addInput("userId", array(
			"name"  => "userId",
			"value" => (isset($_POST["userId"])) ? $_POST["userId"] : null,
			"disabled" => !$loginable,
    		"attr:autocomplete" => "off"
		));
		$this->addInput("password", array(
			"type"  => "password",
			"name"  => "password",
			"value" => (isset($_POST["password"])) ? $_POST["password"] : null,
			"disabled" => !$loginable,
    		"attr:autocomplete" => "off"
		));
		$this->addInput("password_confirm", array(
			"type"  => "password",
			"name"  => "password_confirm",
			"value" => "",
			"disabled" => !$loginable,
    		"attr:autocomplete" => "off"
		));
		$this->addInput("submit_button", array(
			"type"  => "submit",
			"name"  => "login",
			"value" => CMSMessageManager::get("ADMIN_SET"),
			"disabled" => !$loginable
		));

		foreach(array("db", "userId", "password", "password_confirm") as $t){
			DisplayPlugin::toggle("is_message_" . $t, isset($this->_message[$t]) && strlen($this->_message[$t]));
			$this->addLabel("message_" . $t, array(
				"text" => (isset($this->_message[$t])) ? $this->_message[$t] : "",
			));
		}

		$this->addModel("biglogo", array(
    		"src" => CMSUtil::getLogoFile()
    	));
	}

	/**
	 * データベースに接続できるかをチェックする
	 * @return Boolean
	 */
	private function checkLoginable(){

		if(!is_writable(SOY2::RootDir() . "db")){
			$this->_message["db"] = CMSMessageManager::get("ADMIN_DB_CONNECT_FAILURE") . " " . CMSMessageManager::get("ADMIN_DB_NO_ROLE") . " " . realpath(SOY2::RootDir() . "db");

		}else if(SOYCMS_DB_TYPE != "sqlite"){
			try{
				$con = SOY2DAO::_getDataSource();
			}catch(Exception $e){
				$this->_message["db"] = CMSMessageManager::get("ADMIN_DB_CONNECT_FAILURE") . " (" . $e->getMessage() . ")";
			}
		}

		return (!isset($this->_message["db"]) || strlen($this->_message["db"]) == 0);
	}

	/**
	 * すでに初期管理者がいるかどうかをチェックする
	 * @return Boolean
	 */
	private function hasDefaultUser(){
		return SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->hasDefaultUser();
	}
}
