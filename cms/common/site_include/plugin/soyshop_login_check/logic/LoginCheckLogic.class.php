<?php

class LoginCheckLogic extends SOY2LogicBase{
	
	private $siteId;
	private $userId;
	
	function LoginCheckLogic(){}
	
	function isLoggedIn(){
		
		static $isLoggedIn;
		
		if(is_null($isLoggedIn)){
			$old = SOYShopUtil::switchShopMode($this->siteId);
				
			SOY2::import("domain.config.SOYShop_DataSets");
			include_once(SOY2::RootDir() . "base/func/common.php");
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());
				
			SOY2::import("logic.mypage.MyPageLogic");
			$mypage = MyPageLogic::getMyPage();
				
			$isLoggedIn = $mypage->getIsLoggedin();
			$this->userId = (int)$mypage->getUserId();
		
			SOYShopUtil::resetShopMode($old);
		}
		
		return $isLoggedIn;
	}
	
	function getUserId(){
			
		if($this->isLoggedIn()){
			$old = SOYShopUtil::switchShopMode($this->siteId);
				
			SOY2::import("domain.config.SOYShop_DataSets");
			include_once(SOY2::RootDir() . "base/func/common.php");
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());
				
			SOY2::import("logic.mypage.MyPageLogic");
			$mypage = MyPageLogic::getMyPage();				
			$userId = $mypage->getUserId();

			SOYShopUtil::resetShopMode($old);
			
			return $userId;
		}
	}
	
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}
?>