<?php
SOY2::import("domain.admin.Site");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		$this->buildSubMenu();

		$loginableSiteList = $this->getLoginableSiteList();
		$this->createAdd("list", "SiteList", array(
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

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	function getLoginableSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$list = $SiteLogic->getSiteByUserId(UserInfoUtil::getUserId());

		//ルート設定されたサイトを先頭にする
		foreach($list as $id => $site){
			if($site->getIsDomainRoot()){
				unset($list[$id]);
				array_unshift($list, $site);
			}
		}

		return $list;
	}

}

class SiteList extends HTMLList{

	private $domainRootSiteLogic;

	private function getDomainRootSiteLogic(){
		if(!$this->domainRootSiteLogic){
			$this->domainRootSiteLogic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
		}
		return $this->domainRootSiteLogic;
	}

	protected function populateItem($entity){

		$siteName = $entity->getSiteName();
		if($entity->getIsDomainRoot()){
			$siteName = "*" . $siteName;
		}

		$this->addLabel("site_name", array(
			"text" => $siteName
		));

		$this->addLink("login_link", array(
			"link" => $entity->getLoginLink(),
			"id" => ($entity->getSiteType() == Site::TYPE_SOY_CMS) ? "site_id_" . $entity->getSiteId() : "shop_id_" . $entity->getSiteId()
		));

		$this->addLink("site_link", array(
			"link" => $entity->getUrl(),
			"text" => $entity->getUrl(),
			"visible" => (!$entity->getIsDomainRoot())
		));

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$this->addLink("domain_root_site_url", array(
			"link" => $rootLink,
			"text" => $rootLink,
			"visible" => $entity->getIsDomainRoot()
		));

		$this->addLink("auth_link", array(
			"link" => SOY2PageController::createLink("Site.SiteRole." . $entity->getId()),
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP)
		));

		if($entity->getIsDomainRoot()){
			$this->addActionLink("root_site_link", array(
				"link" => SOY2PageController::createLink("Site.SiteRootDetach." . $entity->getId()),
				"text"=>CMSMessageManager::get("ADMIN_ROOT_SETTING_OFF"),
				"onclick"=> 'return confirm("' . CMSMessageManager::get("ADMIN_CONFIRM_ROOT_SETTING_OFF") . '");',
				"id" => "root_site_link_" . $entity->getSiteId(),
			));

		}else{
			$onclick = 'return confirm("' . CMSMessageManager::get("ADMIN_CONFIRM_DOMAIN_ROOT_SETTING") . '");';
			if(file_exists(SOYCMS_TARGET_DIRECTORY . "/index.php")){
				if(true != $this->getDomainRootSiteLogic()->checkCreatedController(SOYCMS_TARGET_DIRECTORY . "/index.php")){
					$onclick = 'return confirm("' . CMSMessageManager::get("ADMIN_CONFIRM_INDEXPHP") . '");';
				}
			}else if(file_exists(SOYCMS_TARGET_DIRECTORY . "/.htaccess")){
				if(true != $this->getDomainRootSiteLogic()->checkCreatedController(SOYCMS_TARGET_DIRECTORY . "/.htaccess")){
					$onclick = 'return confirm("' . CMSMessageManager::get("ADMIN_CONFIRM_HTACCESS") . '");';
				}
			}

			$this->addActionLink("root_site_link", array(
				"link" => SOY2PageController::createLink("Site.SiteRoot." . $entity->getId()),
				"text"=>CMSMessageManager::get("ADMIN_ROOT_SETTING"),
				"onclick"=> $onclick,
				"id" => "root_site_link_" . $entity->getSiteId(),
			));
		}

		$this->addLink("site_detail_link", array(
			"link" => SOY2PageController::createLink("Site.Detail." . $entity->getId()),
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP)
		));

		$this->addLink("remove_link", array(
			"link"	=> SOY2PageController::createLink("Site.Remove." . $entity->getId()),
			"onclick" => $entity->getIsDomainRoot() ? 'alert("' . CMSMessageManager::get("ADMIN_DETACH_ROOT_SETTING_BEFORE_DELETE_SITE") . '");return false;' : "",
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP),
		));
	}
}
