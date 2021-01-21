<?php

class MailPage extends WebPage{

	private  $id;

    function __construct($args) {
		if(!isset($args[0]) || !is_numeric($args[0])) SOY2PageController::jump("User");
    	$this->id = (int)$args[0];

		$user = soyshop_get_user_object($this->id);
		if(is_null($user->getId())) SOY2PageController::jump("User");

    	parent::__construct();

    	self::_buildForm($user);

    	$this->addLink("detail_link", array(
    		"link" => SOY2PageController::createLink("User.Detail." . $this->id)
    	));

    	DisplayPlugin::toggle("wrong_email",(isset($_GET["wrong_email"])));
    	DisplayPlugin::toggle("used_email",(isset($_GET["used_email"])));

    }

	function doPost(){
		if(!soy2_check_token()){
			SOY2PageController::jump("User.Edit.Mail." . $this->id);
		}

		$new_email = @$_POST["Email"];
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		/*
		 * 元のデータを読み込む
		 */
		try{
			$user = soyshop_get_user_object($this->id);
			$user->setMailAddress($new_email);
		}catch(Exception $e){
			SOY2PageController::jump("User.Edit.Mail." . $this->id."?failed");
		}

		/*
		 * 書式チェック
		 */
		if(!$user->isValidEmail()){
			SOY2PageController::jump("User.Edit.Mail." . $this->id . "?wrong_email");
		}

		/*
		 * すでに利用されていれば不可
		 */
		try{
			$dao->getByMailAddress($new_email);
			SOY2PageController::jump("User.Edit.Mail." . $this->id . "?used_email");
		}catch(Exception $e){
			//OK
		}

		/*
		 * 保存
		 */
		try{
			$dao->update($user);
			SOY2PageController::jump("User.Edit.Mail." . $this->id . "?updated");
		}catch(Exception $e){
			SOY2PageController::jump("User.Edit.Mail." . $this->id."?failed");
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

    	$this->addInput("new_mail_address", array(
    		"name" => "Email",
    		"value" => "",
    		"size" => "60"
    	));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("メールアドレス変更", array("User" => SHOP_USER_LABEL . "管理", "User.Detail." . $this->id => SHOP_USER_LABEL . "詳細"));
	}

	function getCSS(){
		return array("./css/admin/user_detail.css");
	}
}
