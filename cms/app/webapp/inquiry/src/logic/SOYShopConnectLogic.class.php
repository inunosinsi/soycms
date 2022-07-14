<?php

class SOYShopConnectLogic extends SOY2LogicBase{

	private $checkVersion;

	function getSOYShopSiteList(){
		if(!$this->checkVersion) $this->checkVersion = $this->checkVersion();

		$sites = array();
		if($this->checkVersion){
			$old = SOYInquiryUtil::switchConfig();
			if(file_exists(SOY2::RootDir() . "domain/SOYShop_SiteDAO.class.php")){
				try{
					//SOY Shopがインストールされていない可能性がある
					$sites = SOY2DAOFactory::create("SOYShop_SiteDAO")->get();
				}catch(Exception $e){
					//
				}
			}
			SOYInquiryUtil::resetConfig($old);
		}

		if(!count($sites)) return array();

		$list = array();
		foreach($sites as $site){
			$list[$site->getId()] = $site->getName();
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
			$shopId = SOYInquiryUtil::getSOYShopSiteId();
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
}
