<?php

class AspAppUserRegisterPage extends WebPage {

	private $errors = array();

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspAppUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AspAppUtil::save($_POST["User"]);
			if(self::validate()){
				header("location:" . AspAppUtil::getPageUri(AspAppUtil::MODE_CONFIRM));
				exit;
			}
		}
	}

	function execute(){
		//隠しモード　管理画面からの登録
		if(isset($_GET["mode"])){
			AspAppUtil::setSession("hidden_mode", $_GET["mode"]);
		}

		parent::__construct();

		DisplayPlugin::toggle("error", count($this->errors));

		$admin = AspAppUtil::get();

		$this->addForm("form");

		$this->addInput("user_name", array(
			"name" => "User[name]",
			"value" => $admin->getName(),
			"attr:required" => "required"
		));

		//登録時はuser_idにも同じ値を入れる
		$this->addInput("mail_address", array(
			"name" => "User[email]",
			"value" => $admin->getEmail(),
			"attr:required" => "required"
		));

		$this->addInput("mail_address_confirm", array(
			"name" => "confirm",
			"value" => (!isset($this->errors["mail_address_confirm_error"])) ? $admin->getEmail() : "",
			"attr:required" => "required"
		));

		$this->addInput("password", array(
			"name" => "User[userPassword]",
			"value" => $admin->getUserPassword(),
			"attr:required" => "required"
		));

		//エラー
		foreach(array(
			"mail_address_confirm",
			"password",
			"mail_address_duplicate",
		) as $t){
			DisplayPlugin::toggle($t . "_error", isset($this->errors[$t . "_error"]));
		}
	}

	private function validate(){
		$admin = AspAppUtil::get(true);
		if($admin["email"] != $_POST["confirm"]){
			$this->errors["mail_address_confirm_error"] = true;
		}

		if(strlen($admin["userPassword"]) < 8){
			$this->errors["password_error"] = true;
		}

		$old = CMSUtil::switchDsn();

		//既に登録されているメールアドレスか？
		try{
			$obj = SOY2DAOFactory::create("admin.AdministratorDAO")->getByEmail($admin["email"]);
			$this->errors["mail_address_duplicate_error"] = true;
		}catch(Exception $e){
			//
		}

		CMSUtil::resetDsn($old);

		return (!count($this->errors));
	}

	/** @ToDo テンプレート編集モード **/
}
