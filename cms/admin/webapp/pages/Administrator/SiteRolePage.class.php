<?php

class SiteRolePage extends CMSUpdatePageBase{

	private $userId;

	function doPost(){

		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("Administrator.SiteRole." . $this->userId);
		}

		if(soy2_check_token()){
			$action = SOY2ActionFactory::createInstance("SiteRole.UpdateAction");
			$result = $action->run();

			if($result->success()){
				$this->addMessage("UPDATE_SUCCESS");
				$this->jump("Administrator.SiteRole." . $this->userId);
			}else{
				SOY2PageController::jump("Administrator.SiteRole." . $this->userId);
			}
		}
	}

	function __construct($arg) {

		$userId = (isset($arg[0])) ? $arg[0] : null;
		if(!UserInfoUtil::isDefaultUser() || strlen($userId) < 1) $userId = UserInfoUtil::getUserId();
		$this->userId = $userId;

		parent::__construct();

		$this->outputMessage();

		$action = SOY2ActionFactory::createInstance("SiteRole.ListAction", array(
			"userId" => $this->userId,
			"limitSite" => true
		));
		$result = $action->run();
		if($result == SOY2Action::FAILED){
			SOY2PageController::jump("Administrator");
		}

		$siteRole = $result->getAttribute("siteRole");
		$this->createAdd("siterole_block", "_common.Site.SiteRoleListComponent", array(
			"site" => $result->getAttribute("siteTitle"),
			"dao" => SOY2DAOFactory::create("admin.SiteDAO"),
			"userId" =>$this->userId,
			"list" => $siteRole
		));

		$this->addForm("siteRoleForm");
		$this->addInput("modify_button", array(
			"type" => "submit",
			"value" => CMSMessageManager::get("ADMIN_CHANGE"),
			"visible" => (count($siteRole) > 0 && UserInfoUtil::isDefaultUser())
		));

		$admin = $result->getAttribute("adminName");
		$this->addLabel("user_name", array(
			"text"=>$admin->getUserId() . CMSMessageManager::get("ADMIN_MESSAGE_SITE_ROLE_LIST")
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
