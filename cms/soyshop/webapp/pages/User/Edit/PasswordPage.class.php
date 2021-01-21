<?php

class PasswordPage extends WebPage{

	private $id;
	private $session;

    function __construct($args) {
		SOY2::import("domain.config.SOYShop_ShopConfig");

		if(!isset($args[0]) || !is_numeric($args[0])) SOY2PageController::jump("User");
    	$this->id = (int)$args[0];

		$user = soyshop_get_user_object($this->id);
		if(is_null($user->getId())) SOY2PageController::jump("User");

    	$this->session = SOY2ActionSession::getUserSession();

    	parent::__construct();

		//パスワードの文字数
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->addLabel("password_count", array(
			"text" => SOYShop_ShopConfig::load()->getPasswordCount()
		));

    	self::_buildForm($user);

    	$this->addLink("detail_link", array(
    		"link" => SOY2PageController::createLink("User.Detail." . $this->id)
    	));

    	DisplayPlugin::toggle("too_short", (isset($_GET["too_short"])));

    	$this->session->setAttribute("user.edit.password.password", null);

    }

	function doPost(){
		if(!soy2_check_token()){
			SOY2PageController::jump("User.Edit.Password." . $this->id);
		}

		$password = @$_POST["Password"];

		/*
		 * パスワードは８文字以上必須
		 */
		if(strlen($password) < SOYShop_ShopConfig::load()->getPasswordCount()){
			$this->session->setAttribute("user.edit.password.password",$password);
			SOY2PageController::jump("User.Edit.Password." . $this->id."?too_short");
		}

		try{
			//元のデータを読み込む
			$user = soyshop_get_user_object($this->id);
			$user->setPassword($user->hashPassword($password));
			SOY2DAOFactory::create("user.SOYShop_UserDAO")->update($user);
			SOY2PageController::jump("User.Edit.Password." . $this->id."?updated");
		}catch(Exception $e){
			SOY2PageController::jump("User.Edit.Password." . $this->id."?failed");
		}

	}

 	private function _buildForm(SOYShop_User $user){

    	$this->addForm("detail_form");

    	$this->addLabel("id", array(
    		"text" => $user->getId(),
    	));

    	$this->addLabel("mail_address", array(
    		"text" => $user->getMailAddress()
    	));
    	$this->addLabel("name", array(
    		"text" => $user->getName(),
    	));

    	$this->addLabel("register_date", array(
    		"text" => is_null($user->getRegisterDate()) ? "" : date("Y-m-d H:i:s", $user->getRegisterDate()),
    	));

    	$this->addLabel("update_date", array(
    		"text" => is_null($user->getUpdateDate()) ? "" : date("Y-m-d H:i:s", $user->getUpdateDate()),
    	));

    	$this->addModel("password_is_registered", array(
    		"visible" => strlen($user->getPassword())
    	));
    	$this->addModel("password_is_not_registered", array(
    		"visible" => !strlen($user->getPassword())
    	));

    	$this->addInput("password", array(
    		"name" => "Password",
    		"value" => $this->session->getAttribute("user.edit.password.password"),
    		"size" => "60"
    	));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("パスワード変更", array("User" => SHOP_USER_LABEL . "管理", "User.Detail." . $this->id => SHOP_USER_LABEL . "詳細"));
	}

	function getCSS(){
		return array("./css/admin/user_detail.css");
	}
}
