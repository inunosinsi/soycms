<?php

class DetailPage extends CMSUpdatePageBase{

	private $adminId;
	private $failed = false;

	function doPost(){
		if(UserInfoUtil::getUserId() != $this->adminId && !UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator");
			exit;
		}

		if(soy2_check_token() && $this->updateAdministrator()){
			$this->addMessage("UPDATE_SUCCESS");
			$this->jump("Administrator.Detail." . $this->adminId);
			exit;
		}else{
			$this->failed = true;
		}
	}

	function __construct($arg) {
		$adminID = (isset($arg[0])) ? $arg[0] : null;
		if(!UserInfoUtil::isDefaultUser() || strlen($adminID) < 1) $adminID = UserInfoUtil::getUserId();

		$result = $this->run("Administrator.DetailAction", array("adminId" => $adminID));
		$admin = $result->getAttribute("admin");
		$this->adminId = $adminID;

		parent::__construct();

		$showInputForm = UserInfoUtil::isDefaultUser() || $this->adminId == UserInfoUtil::getUserId();

		$this->addInput("userId", array(
			"name" => "userId",
			"value"=>$admin->getUserId(),
			"visible"=> $showInputForm
		));
		$this->addInput("name", array(
			"name" => "name",
			"value"=>$admin->getName(),
			"visible"=> $showInputForm
		));
		$this->addInput("email", array(
			"name" => "email",
			"value"=>$admin->getEmail(),
			"visible"=> $showInputForm
		));

		$this->addLabel("userId_text", array(
			"text"=>(strlen($admin->getUserId()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getUserId(),
			"visible"=> !$showInputForm
		));
		$this->addLabel("name_text", array(
			"text"=>(strlen($admin->getName()) == 0 ) ? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getName(),
			"visible"=> !$showInputForm
		));
		$this->addLabel("email_text", array(
			"text"=>(strlen($admin->getEmail()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getEmail(),
			"visible"=> !$showInputForm
		));

		$this->addModel("show_userid_input", array(
			"attr:class" => $showInputForm ? "" : "no_example"
		));
		$this->addModel("show_userid_input_example", array(
			"visible" => $showInputForm
		));

		$this->addModel("button_toggle", array(
			"visible"=> $showInputForm
		));


		$this->addForm("detailForm");


		$this->addModel("error", array(
			"visible" => $this->failed
		));

		$messages = CMSMessageManager::getMessages();
		$this->addLabel("message", array(
			"text" => implode("\n", $messages),
			"visible" => !empty($messages)
		));

		$this->addModel("has_message_or_error",array(
			"visible" => $this->failed || !empty($messages),
		));
	}

	function setAdminId($adminId) {
		$this->adminId = $adminId;
	}

	function updateAdministrator(){
		$result = $this->run("Administrator.UpdateAction", array("adminId" => $this->adminId));
		return $result->success();
	}
}
?>