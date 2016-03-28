<?php
SOY2::import("domain.admin.Site");

class ApplicationPage extends CMSWebPageBase{

	function ApplicationPage(){
		
		//appIdが存在していない場合はトップページに遷移させる
		if(!isset($_GET["appId"])){
			SOY2PageController::jump();
		}
		
		WebPage::WebPage();

		$loginableSiteList = $this->getLoginableSiteList();
		
		//ログイン可能ページが存在していない場合はトップページに遷移させる
		if(count($loginableSiteList) === 0){
			SOY2PageController::jump();
		}
		
		$appInfo = $this->getApplicationIniFile();
		$this->addLabel("app_name", array(
			"text" => (isset($appInfo["title"])) ? $appInfo["title"] : ""
		));
		
		$this->createAdd("list", "SiteList", array(
			"list" => $loginableSiteList
		));

		$this->addModel("no_site", array(
			"visible" => (count($loginableSiteList) < 1)
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
		if(isset($array["port"])) $host .=   ":" . $array["port"];

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
			"link" => $entity->getLoginLink() . "?appId=" . $_GET["appId"]
		));

		$siteLink = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/' . $entity->getSiteId();
		$this->addLink("site_link", array(
			"link" => $entity->getUrl(),
			"text" => $this->replaceTooLongHost($entity->getUrl())
		));

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$this->addLink("domain_root_site_url", array(
			"link" => $rootLink,
			"text" => $this->replaceTooLongHost($rootLink),
			"visible" => $entity->getIsDomainRoot()
		));
	}
}
?>