<?php

class MailPage extends WebPage{

	var $id;

    function __construct($args) {
    	$id = @$args[0];
    	$this->id = $id;

    	parent::__construct();

    	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

    	try{
    		$shopUser = $dao->getById($id);
    	}catch(Exception $e){
    		SOY2PageController::jump("User.Detail." . $this->id);
    		exit;
    	}

    	$this->buildForm($shopUser);

    	$this->createAdd("detail_link","HTMLLink", array(
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
			$user = $dao->getById($this->id);
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

	function getCSS(){
		return array("./css/admin/user_detail.css");
	}

   function buildForm(SOYShop_User $user){

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


}

