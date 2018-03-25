<?php

class SiteRolePage extends CMSUpdatePageBase{

	private $siteId;

	function doPost(){

		if(soy2_check_token()){
			$action = SOY2ActionFactory::createInstance("SiteRole.UpdateAction");
	    	$result = $action->run();

	    	if($result->success()){
				$this->addMessage("UPDATE_SUCCESS");
	    		$this->jump("Site.SiteRole." . $this->siteId);
	    	}else{
	    		$this->jump("Site.SiteRole." . $this->siteId);
	    	}
		}
	}

    function __construct($arg) {

    	$siteId = (isset($arg[0])) ? $arg[0] : null;
    	if(is_null($siteId)){
    		SOY2PageController::jump("Site");
    	}
    	$this->siteId = $siteId;

    	if(!UserInfoUtil::isDefaultUser()){
    		SOY2PageController::jump("Site");
    	}

    	parent::__construct();

    	$action = SOY2ActionFactory::createInstance("SiteRole.ListAction", array(
    		"siteId" => $siteId
    	));
    	$result = $action->run();

    	if($result == SOY2Action::FAILED){
    		SOY2PageController::jump("Site");
    	}

    	$siteRole = $result->getAttribute("siteRole");
    	$userName = $result->getAttribute("adminName");

    	$this->createAdd("siterole_block", "SiteRoleList", array(
    		"list" => $siteRole,
    		"user" => $userName,
    		"siteId" => $this->siteId
    	));

    	$this->addForm("siteRoleForm");

    	$siteInfo = $result->getAttribute("siteTitle");
    	$this->addLabel("site_title", array(
    		"text" => $siteInfo->getSiteId() . CMSMessageManager::get("ADMIN_MASSAGE_ADMIN_LIST")
    	));

    	$this->addInput("modify_button", array(
    		"type" => "submit",
    		"value" => CMSMessageManager::get("ADMIN_CHANGE"),
    		"visible" => (count($siteRole) > 0)
    	));

		$messages = CMSMessageManager::getMessages();
    	$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
    }
}

class SiteRoleList extends HTMLList{

	private $user;
	private $siteId;

	function setUser($user){
		$this->user = $user;
	}

	function setSiteId($siteId){
		$this->siteId = $siteId;
	}


	protected function populateItem($entity, $key){
		$this->addLabel("user_name", array(
			"text" => $this->user[$key]
		));

		$this->addSelect("site_role", array(
			"options" => SiteRole::getSiteRoleLists(),
			"name" => "siteRole[" . $key . "][" . $this->siteId . "]",
			"indexOrder" => true,
			"selected" => (int)$entity
		));
	}
}
