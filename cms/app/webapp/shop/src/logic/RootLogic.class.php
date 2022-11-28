<?php

class RootLogic extends SOY2LogicBase {

	/** フロントコントローラーの再生成周り **/
	function createSOYShopController(Site $site){

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		//Shop関連の定数設定
		$webappDir = ShopUtil::setShopSiteConfig($site);

		SOY2::import("util.CMSUtil");

		$htaccessPath = SOYSHOP_SITE_DIRECTORY . ".htaccess";
	   	CMSUtil::createBackup($htaccessPath);
	   	$filename = SOYSHOP_SITE_DIRECTORY . "index.php";
	   	CMSUtil::createBackup($filename);

		ShopUtil::resetConfig($old);

		include($webappDir . "src/logic/init/InitLogic.class.php");
		$logic = new InitLogic();

		try{
			$logic->initController(true);
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * 自身のサイトがルート設定しているか
	 * @return boolean
	 */
	function checkIsRootSite($siteId){
		return (ShopUtil::getSiteById($siteId)->getIsDomainRoot() == 1);
	}

	/**
	 * ルート設定されたサイトがあるか
	 * @return boolean
	 */
	function checkHasRootSite(){
		$sites = ShopUtil::getSites();
		foreach($sites as $site){
			if($site->getIsDomainRoot() == 1) return true;
		}
		return false;
	}

	function updateDomainRootSite(Site $site, $htaccess = null){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		$dao->begin();
		$dao->resetDomainRootSite();
		$dao->updateDomainRootSite($site->getId());
		$dao->commit();

		try{
			$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
			$logic->create();
			if($htaccess) $logic->createHtaccess($htaccess);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		//キャッシュ削除
		SOY2::import("util.CMSUtil");
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$sites = $SiteLogic->getSiteList();
		foreach($sites as $site){
			CMSUtil::unlinkAllIn($site->getPath().".cache/");
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	function detachDomainRootSite($id,$htaccess = null){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		$dao->begin();
		$dao->resetDomainRootSite();
		$dao->commit();

		try{
			$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
			$logic->delete();

			if($htaccess)$logic->createHtaccess($htaccess);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		//キャッシュ削除
		SOY2::import("util.CMSUtil");
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$site = $SiteLogic->getById($id);
		if($site){
			CMSUtil::unlinkAllIn($site->getPath().".cache/");
		}

		ShopUtil::resetConfig($old);

		return $res;
	}
}
