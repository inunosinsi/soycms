<?php

class ChangePasswordPage extends CMSUpdatePageBase{

	private $failed = false;

	function doPost(){

		if(soy2_check_token() && $this->updatePassword()){
			$this->addMessage("CHANGE_PASSWORD_SUCCESS");
			$this->jump("Administrator");
		}else{
			$this->failed = true;
		}
	}

    function __construct(){
    	parent::__construct();
    	$this->addForm("change_password_form");

    	$this->addModel("error", array(
    		"visible" => $this->failed
    	));

    }

    /**
     * 現在の管理者のパスワードを変更します
     * Administrator
     * @return boolean
     */
    function updatePassword(){
    	$action = SOY2ActionFactory::createInstance("Administrator.ChangePasswordAction");
    	$result = $action->run();

    	return $result->success();
    }
}
?>