<?php

class AspUserRegisterPage extends WebPage {

	private $errors = array();

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AspUtil::save($_POST["User"]);
			AspUtil::saveSiteId($_POST["siteId"]);
			if(self::validate()){
				header("location:" . AspUtil::getPageUri(AspUtil::MODE_CONFIRM));
				exit;
			}
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", count($this->errors));

		$admin = AspUtil::get();
		
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

		$this->addLabel("site_url", array(
			"text" => AspUtil::getSiteUrl()
		));

		//site_idの代わりに使う
		$this->addInput("site_id", array(
			"name" => "siteId",
			"value" => AspUtil::getSiteId(),
			"style" => "ime-mode:inactive",
			"attr:required" => "required",
			"attr:pattern" => "^[a-zA-Z0-9]+$"
		));

		//エラー
		foreach(array(
			"mail_address_confirm",
			"password",
			"mail_address_duplicate",
			"site_id_duplicate"
		) as $t){
			DisplayPlugin::toggle($t . "_error", isset($this->errors[$t . "_error"]));
		}
	}

	private function validate(){
		$admin = AspUtil::get(true);
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

		//既に登録されているsite_idか？
		try{
			$obj = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId(AspUtil::getSiteId());
			$this->errors["site_id_duplicate_error"] = true;
		}catch(Exception $e){
			//
		}

		CMSUtil::resetDsn($old);

		return (!count($this->errors));
	}

	/** @ToDo テンプレート編集モード **/
}
