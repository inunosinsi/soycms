<?php

class SiteListComponent extends HTMLList {

	private $domainRootSiteLogic;

	private function getDomainRootSiteLogic(){
		if(!$this->domainRootSiteLogic){
			$this->domainRootSiteLogic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
		}
		return $this->domainRootSiteLogic;
	}

	protected function populateItem($entity){

		$siteName = $entity->getSiteName();
		if($entity->getIsDomainRoot()) $siteName = "*" . $siteName;

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
			"id" => ($entity->getSiteType() == Site::TYPE_SOY_CMS) ? "remove_site_id_" . $entity->getSiteId() : "shop_id_" . $entity->getSiteId(),
			"onclick" => $entity->getIsDomainRoot() ? 'alert("' . CMSMessageManager::get("ADMIN_DETACH_ROOT_SETTING_BEFORE_DELETE_SITE") . '");return false;' : "",
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP),
		));
	}
}
