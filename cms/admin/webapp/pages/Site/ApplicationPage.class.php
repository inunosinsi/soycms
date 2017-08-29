<?php
SOY2::import("domain.admin.Site");

class ApplicationPage extends CMSWebPageBase{

	function __construct(){

		//appIdが存在していない場合はトップページに遷移させる
		if(!isset($_GET["appId"])){
			SOY2PageController::jump();
		}

		parent::__construct();

		$loginableSiteList = $this->getLoginableSiteList();

		$appInfo = $this->getApplicationIniFile();
		$this->addLabel("app_name", array(
			"text" => (isset($appInfo["title"])) ? $appInfo["title"] : ""
		));

		$this->createAdd("list", "SiteList", array(
			"list" => $loginableSiteList
		));

		$this->addModel("has_site", array(
				"visible" => count($loginableSiteList)
		));
		$this->addModel("no_site", array(
				"visible" => ! count($loginableSiteList)
		));
	}

	function getApplicationIniFile(){
		$appLogic = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic");
		return $appLogic->getApplication($_GET["appId"]);
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
				"link" => $entity->getLoginLink(). "?appId=" . $_GET["appId"],
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

		//SOY Shopのサイトは不可
		if($entity->getSiteType() != Site::TYPE_SOY_CMS) return false;
	}
}
