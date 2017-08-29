<?php

class RemovePage extends CMSUpdatePageBase{

	private $adminId;
	private $failed = false;
		var $id;

	function doPost() {

		if(soy2_check_token()){
		  	$action = SOY2ActionFactory::createInstance("Administrator.DeleteAction",array(
				"id" => $this->adminId
			));
			$result = $action->run();

			if($result->success()){
				$this->addMessage("REMOVE_SUCCESS");
				$this->jump("Administrator");
			}else{
				$this->addMessage("REMOVE_FAILED");
				$this->reload();
			}
		}

		$this->jump("Administrator");
	}

	function __construct($arg){
		if(!UserInfoUtil::isDefaultUser() || count($arg) < 1){
			SOY2PageController::jump("Administrator");
		}

		$this->adminId = (isset($arg[0])) ? $arg[0] : null;

		$result = $this->run("Administrator.DetailAction", array("adminId" => $this->adminId));
		$admin = $result->getAttribute("admin");

		if($result->success()){
			//
		}else{
			$this->jump("Administrator");
		}

		parent::__construct();

		$this->outputMessage();

		$this->addLabel("userId", array(
			"text"	=>	$admin->getUserId()
		));

		$this->addLabel("name_text", array(
			"text" => (strlen($admin->getName()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getName(),
		));

		$this->addLabel("email_text", array(
			"text" => (strlen($admin->getEmail()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getEmail(),
		));

		$this->addForm("removeForm");

		$this->addModel("error", array(
			"visible" => $this->failed
		));
	}

	function outputMessage(){
		$messages = CMSMessageManager::getMessages();
		$this->addLabel("message", array(
			"text" => implode("\n", $messages),
			"visible" => !empty($messages)
		));
		$this->addModel("has_message", array(
			"visible" => !empty($messages)
		));
	}
}
