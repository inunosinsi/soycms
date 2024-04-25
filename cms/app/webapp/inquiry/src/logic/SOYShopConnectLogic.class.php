<?php

class SOYShopConnectLogic extends SOY2LogicBase{

	private $checkVersion;

	function getSOYShopSiteList(){
		if(!$this->checkVersion) $this->checkVersion = $this->checkVersion();
		
		$sites = array();
		if($this->checkVersion){
			$old = SOYInquiryUtil::switchConfig();
			// SOYShop_SiteDAOがある場合
			if(file_exists(SOY2::RootDir() . "domain/SOYShop_SiteDAO.class.php")){
				try{
					//SOY Shopがインストールされていない可能性がある
					$sites = SOY2DAOFactory::create("SOYShop_SiteDAO")->get();
				}catch(Exception $e){
					//
				}
			}

			if(!count($sites)){
				// SOYShop_SiteDAO廃止用のコード
				CMSApplication::switchRoot();
				CMSApplication::switchDomain();
				try{
					$sites = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_SHOP);
				}catch(Exception $e){
					//
				}
			}

			SOYInquiryUtil::resetConfig($old);
		}

		if(!count($sites)) return array();

		$list = array();
		foreach($sites as $site){
			$list[$site->getId()] = (method_exists($site, "getSiteName")) ? $site->getSiteName() : $site->getName();
		}

		return $list;
	}

	/**
	 * SOY Shopのバージョンを調べる
	 * 1.8.0以降ならばtrueを返す
	 */
	function checkVersion(){

		//SOY Shopのiniファイル
		$old = SOYInquiryUtil::switchConfig();
		$iniForSoyShop = dirname(SOY2::RootDir()) . "/application.ini";
		SOYInquiryUtil::resetConfig($old);

		//soyshopのiniファイルが無い
		if(!is_readable($iniForSoyShop)){
			return false;
		}
		$text = file_get_contents($iniForSoyShop);

		//iniファイルにバージョン情報がない
		if(!preg_match('/version = \"(.*)\"/', $text, $tmp)){
			return false;
		}

		$version = $tmp[1];
		if($version === "SOYSHOP_VERSION"){//開発環境
			return true;
		}else{
			//バージョンが1.8.0以降であることを確認する
			return version_compare($version, "1.8.0", ">=");
		}

		return false;
	}

	function getSOYShopUser(){
		static $user;
		if(is_null($user)){
			$shopId = self::_getSOYShopSiteId();
			if(!is_string($shopId)) $shopId = "";
			$old = SOYInquiryUtil::switchSOYShopConfig($shopId);

			SOY2::import("domain.config.SOYShop_DataSets");
			include_once(SOY2::RootDir() . "base/func/common.php");
			include_once(SOY2::RootDir() . "base/func/dao.php");
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());

			SOY2::import("logic.mypage.MyPageLogic");
			$mypage = MyPageLogic::getMyPage();

			$isLoggedIn = $mypage->getIsLoggedin();
			$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			$userId = ($isLoggedIn) ? $mypage->getUserId() : 0;
			$user = soyshop_get_user_object($userId);

			SOYInquiryUtil::resetConfig($old);
		}
		return $user;
	}

	/**
	 * @param int
	 * @return string
	 */
	function getItemNameByItemId(int $itemId){
		if($itemId <= 0) return 0;

		$old = SOYInquiryUtil::switchSOYShopConfig(self::_getSOYShopSiteId());
		$name = "";

		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
			if($item->isPublished()) $name = trim($item->getOpenItemName());
		}catch(Exception $e){
			//
		}
		SOYInquiryUtil::resetConfig($old);
		return htmlspecialchars($name, ENT_QUOTES, "UTF-8");
	}

	function setSOYShopSiteIdConstant(){
		$itemId = (self::_getSOYShopSiteId() > 0) ? (int)SOYInquiryUtil::getParameter("item_id") : 0;
		if(!defined("SOYSHOP_ITEM_ID")) define("SOYSHOP_ITEM_ID", $itemId);
	}

	/**
	 * @return string
	 */
	private function _getSOYShopSiteId(){
		if(!defined("SOYSHOP_SITE_ID") && defined("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID")){
			$old = SOYInquiryUtil::switchConfig();

			try{
				$siteId = (string)SOY2DAOFactory::create("SOYShop_SiteDAO")->getById(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID)->getSiteId();
			}catch(Exception $e){
				$siteId = "";
			}
			if(!strlen($siteId)){
				// SOYShop_SiteDAO廃止用のコード
				CMSApplication::switchRoot();
				CMSApplication::switchDomain();
				try{
					$siteId = (string)SOY2DAOFactory::create("admin.SiteDAO")->getById(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID)->getSiteId();
				}catch(Exception $e){
					//
				}
			}
			define("SOYSHOP_SITE_ID", $siteId);

			SOYInquiryUtil::resetConfig($old);
		}

		return SOYSHOP_SITE_ID;
	}
}
