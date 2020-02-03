<?php

class LoginCheckLogic extends SOY2LogicBase{

	private $siteId;
	private $userId;

	function __construct(){
		SOY2::import("util.SOYShopUtil");
	}

	function isLoggedIn(){
		return self::_mypage()->getIsLoggedin();
	}

	function getUserId(){
		return (self::_mypage()->getIsLoggedin()) ? (int)self::_mypage()->getUserId() : null;
	}

	private function _mypage(){
		static $mypage;
		if(is_null($mypage)){
			$old = SOYShopUtil::switchShopMode($this->siteId);

			SOY2::import("domain.config.SOYShop_DataSets");
			include_once(SOY2::RootDir() . "base/func/common.php");
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());

			SOY2::import("logic.mypage.MyPageLogic");
			$mypage = MyPageLogic::getMyPage();

			SOYShopUtil::resetShopMode($old);
		}
		return $mypage;
	}

	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}
