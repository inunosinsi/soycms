<?php

class PasswordPage extends WebPage{

	var $id;
	var $session;

    function __construct($args) {
    	$id = (isset($args[0])) ? $args[0] : null;
    	$this->id = $id;

    	$this->session = SOY2ActionSession::getUserSession();

    	WebPage::__construct();

    	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

    	try{
    		$shopUser = $dao->getById($id);
    	}catch(Exception $e){
    		SOY2PageController::jump("User.Detail." . $this->id);
    		exit;
    	}

    	$this->buildForm($shopUser);

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
		if(strlen($password) < 8){
			$this->session->setAttribute("user.edit.password.password",$password);
			SOY2PageController::jump("User.Edit.Password." . $this->id."?too_short");
		}


		try{
			//元のデータを読み込む
			$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			$user = $dao->getById($this->id);
			$user->setPassword($user->hashPassword($password));
			$dao->update($user);
			SOY2PageController::jump("User.Edit.Password." . $this->id."?updated");
		}catch(Exception $e){
			SOY2PageController::jump("User.Edit.Password." . $this->id."?failed");
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

    	$this->addModel("password_is_registered", array(
    		"visible" => strlen($user->getPassword())
    	));
    	$this->createAdd("password_is_not_registered","HTMLModel", array(
    		"visible" => !strlen($user->getPassword())
    	));

    	$this->addInput("password", array(
    		"name" => "Password",
    		"value" => $this->session->getAttribute("user.edit.password.password"),
    		"size" => "60"
    	));
    }
}
?>