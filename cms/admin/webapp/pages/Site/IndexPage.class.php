<?php
SOY2::import("domain.admin.Site");

class IndexPage extends CMSWebPageBase{

	function IndexPage(){
		WebPage::WebPage();

		if(!UserInfoUtil::isDefaultUser()){
			DisplayPlugin::hide("only_default_user");
		}

		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Site.Create")
		));

		$loginableSiteList = $this->getLoginableSiteList();
		$this->createAdd("list", "SiteList", array(
			"list" => $loginableSiteList
		));

		$this->addModel("no_site", array(
			"visible" => (count($loginableSiteList) < 1)
		));

		$messages = CMSMessageManager::getMessages();
		$errores = CMSMessageManager::getErrorMessages();
    	$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
		$this->addLabel("error", array(
			"text" => implode($errores),
			"visible" => (count($errores) > 0)
		));
	}

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	function getLoginableSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteByUserId(UserInfoUtil::getUserId());
	}

}

class SiteList extends HTMLList{

	var $domainRootSiteLogic;

	function getDomainRootSiteLogic(){
		if(!$this->domainRootSiteLogic){
			$this->domainRootSiteLogic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
		}
		return $this->domainRootSiteLogic;
	}

	function replaceTooLongHost($url){

		$array = parse_url($url);

		$host = $array["host"];
		if(isset($array["port"]))$host .=   ":" . $array["port"];

		if(strlen($host) > 30){
			$host = mb_strimwidth($host, 0, 30, "...");
		}

		$url = $array["scheme"] . "://" . $host . $array["path"];

		return $url;

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

		$siteLink = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/' . $entity->getSiteId();
		$this->addLink("site_link", array(
			"link" => $entity->getUrl(),
			"text" => $this->replaceTooLongHost($entity->getUrl()),
			"visible" => (!$entity->getIsDomainRoot())
		));

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$this->addLink("domain_root_site_url", array(
			"link" => $rootLink,
			"text" => $this->replaceTooLongHost($rootLink),
			"visible" => $entity->getIsDomainRoot()
		));

		$this->addLink("auth_link", array(
			"link" => SOY2PageController::createLink("Site.SiteRole." . $entity->getId()),
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP)
		));

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

    	if($entity->getIsDomainRoot()){
    		$this->addActionLink("root_site_link", array(
				"link" => SOY2PageController::createLink("Site.SiteRootDetach." . $entity->getId()),
				"text"=>CMSMessageManager::get("ADMIN_ROOT_SETTING_OFF"),
				"onclick"=> 'return confirm("' . CMSMessageManager::get("ADMIN_CONFIRM_ROOT_SETTING_OFF") . '");',
				"id" => "root_site_link_" . $entity->getSiteId(),
			));

    	}else{
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
			"link"    => SOY2PageController::createLink("Site.Remove." . $entity->getId()),
			"onclick" => $entity->getIsDomainRoot() ? 'alert("' . CMSMessageManager::get("ADMIN_DETACH_ROOT_SETTING_BEFORE_DELETE_SITE") . '");return false;' : "",
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP)
		));
	}
}
?>