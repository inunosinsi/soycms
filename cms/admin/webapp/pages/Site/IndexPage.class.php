<?php
SOY2::import("domain.admin.Site");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		self::buildSubMenu();

		$loginableSiteList = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getLoginableSiteListByUserId(UserInfoUtil::getUserId());
		$this->createAdd("list", "_common.Site.SiteListComponent", array(
			"list" => $loginableSiteList
		));

		$this->addModel("has_site", array(
				"visible" => count($loginableSiteList)
		));
		$this->addModel("no_site", array(
				"visible" => ! count($loginableSiteList)
		));

		$messages = CMSMessageManager::getMessages();
		$errors = CMSMessageManager::getErrorMessages();
		$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
		$this->addLabel("error", array(
			"text" => implode($errors),
			"visible" => (count($errors) > 0)
		));

		$this->addModel("has_message_or_error", array(
			"visible" => count($messages) || count($errors),
		));
	}

	private function buildSubMenu(){
		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Site.Create")
		));

		$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");

		$this->addLink("edit_indexphp", array(
			"link"    => SOY2PageController::createLink("Site.EditControllerForRoot"),
		));
		$this->addModel("can_edit_indexphp", array(
			"visible" => UserInfoUtil::isDefaultUser() && file_exists($logic->getPathOfController()),
		));

		$this->addLink("edit_htaccess", array(
			"link"    => SOY2PageController::createLink("Site.EditHtaccessForRoot"),
		));
		$this->addModel("can_edit_htaccess", array(
			"visible" => UserInfoUtil::isDefaultUser() && file_exists($logic->getPathOfHtaccess()),
		));
	}
}
