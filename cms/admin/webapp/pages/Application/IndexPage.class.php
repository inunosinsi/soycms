<?php
SOY2::import("domain.admin.Site");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		//アプリケーション
		$applications = $this->getLoginiableApplicationLists();
		$this->createAdd("application_list", "ApplicationList", array(
			"list" => $applications
		));

		$this->addModel("no_application", array(
			"visible" => (count($applications) < 1)
		));

	}

	/**
	 * 2008-07-24 ログイン可能なアプリケーションを読み込む
	 */
	function getLoginiableApplicationLists(){
		$appLogic = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic");
		if(UserInfoUtil::isDefaultUser()){
			return $appLogic->getApplications();
		}else{
			return $appLogic->getLoginableApplications(UserInfoUtil::getUserId());
		}
	}
}

class ApplicationList extends HTMLList{
	protected function populateItem($entity, $key){
		$this->addLabel("name", array(
			"text" => $entity["title"]
		));

		$this->addLink("login_link", array(
			"link" => SOY2PageController::createRelativeLink("../app/index.php/" . $key)
		));
		$this->addLabel("description", array(
			"text" => $entity["description"]
		));
		$this->addLabel("version", array(
			"text" => $entity["version"],
			"visible" => (isset($entity["version"])),
		));
		$this->addLink("auth_link", array(
			"link" => SOY2PageController::createLink("Application.Role") . "?app_id=" . $key
		));
	}
}
?>