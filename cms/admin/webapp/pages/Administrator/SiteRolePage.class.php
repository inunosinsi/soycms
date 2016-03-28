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

    function SiteRolePage($arg) {

    	$userId = (isset($arg[0])) ? $arg[0] : null;
    	if(!UserInfoUtil::isDefaultUser() || strlen($userId) < 1) $userId = UserInfoUtil::getUserId();
    	$this->userId = $userId;

    	WebPage::WebPage();

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
    	$this->createAdd("siterole_block", "SiteRoleList", array(
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
    }

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	function getLoginableSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteByUserId(UserInfoUtil::getUserId());
	}
	/**
	 * 現在のユーザIDからログイン可能なサイトのIDのリストを取得する
	 */
	function getLoginableSiteIds(){
		$ids = array();
		$list = $this->getLoginableSiteList();
		foreach($list as $key => $site){
			$ids[] = $site->getId();
		}
		return $ids;
	}
}

class SiteRoleList extends HTMLList{

	private $site;
	private $userId;
	private $dao;

	function setSite($site){
		$this->site = $site;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}

	function setDao($dao){
		$this->dao = $dao;
	}


	protected function populateItem($entity, $key){

		$this->addLabel("site_name", array(
			"text"    => $this->site[$key],
		));


		$this->addSelect("site_role", array(
			"options" => SiteRole::getSiteRoleLists(),
			"name" => "siteRole[" . $this->userId . "][" . $key . "]",
			"indexOrder" => true,
			"selected" => (int)$entity,
			"visible"=>UserInfoUtil::isDefaultUser(),
			"disabled" => ($this->getSiteType($key) == 2)
		));

		$list = SiteRole::getSiteRoleLists();
		$text = $list[(int)$entity];
		$this->addLabel("site_role_text", array(
			"text" => $text,
			"visible" => !UserInfoUtil::isDefaultUser()
		));
	}

	function getSiteType($key){

		try{
			$site = $this->dao->getById($key);
		}catch(Exception $e){
			$site = new Site();
		}

		return $site->getSiteType();
	}

}
?>