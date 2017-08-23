<?php
class ChangePasswordPage extends CMSUpdatePageBase{

	private $error_str;

	public function setError_str($str){
		$this->error_str = $str;
	}

	public function doPost(){

		if(soy2_check_token()){
			$result = SOY2ActionFactory::createInstance("Administrator.ChangePasswordAction")->run();

			$form = $result->getAttribute("form");

			if($result->success()){
				$this->jump("Account", array("passwordChanged"=>true));
			}else{

				if($form->hasError()){
					$str = CMSMessageManager::get("ADMIN_NEW_PASSWORD_FORMAT_WRONG");
				}else if($form->newPassword != $form->newPasswordConfirm){
					$str = CMSMessageManager::get("ADMIN_NEW_PASSWORDS_NOT_SAME");
				}else{
					$str = CMSMessageManager::get("ADMIN_OLD_PASSWORD_WRONG");
				}

				$this->jump("Account.ChangePassword", array("error_str" => $str));
			}
		}
	}

	function __construct(){
		parent::__construct();

		$this->addForm("changeform");
		$this->buildForm();
	}

	private function buildForm(){

		$this->addInput("password", array(
			"name" => "newPassword",
			"value" => "",
			"type" => "password",
			"required" => true,
		));

		$this->addInput("password_confirm", array(
			"name" => "newPasswordConfirm",
			"value" => "",
			"type" => "password",
			"required" => true,
		));

		$this->addInput("old_password", array(
			"name"=>"oldPassword",
			"value"=>"",
			"type"=>"password",
			"required" => true,
		));

		$this->addLabel("error_msg", array(
				"text"=>$this->error_str,
		));
		$this->addModel("has_error", array(
				"visible"=>(strlen($this->error_str))
		));
	}
}
