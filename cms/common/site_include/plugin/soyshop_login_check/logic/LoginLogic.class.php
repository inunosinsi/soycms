<?php

class LoginLogic extends SOY2LogicBase {
	
	private $siteId;
	
	function LoginkLogic(){}
	
	function getLoginPageUrl(){		
		$old = SOYShopUtil::switchShopMode($this->siteId);
		
		SOY2::import("domain.config.SOYShop_DataSets");
		include_once(SOY2::RootDir() . "base/func/common.php");
		
		$loginPageUrl = soyshop_get_mypage_url() . "/login";
		
		SOYShopUtil::resetShopMode($old);
		
		return $loginPageUrl;
	}
	
	function getLogoutPageUrl(){		
		$old = SOYShopUtil::switchShopMode($this->siteId);
		
		SOY2::import("domain.config.SOYShop_DataSets");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		include_once(SOY2::RootDir() . "base/func/common.php");
		
		$config = SOYShop_ShopConfig::load();
		$logoutPageUrl = soyshop_get_mypage_url() . "/logout";
		
		if($config->getDisplayPageAfterLogout() == 1){
			$logoutPageUrl .= "?r=" . soyshop_remove_get_value(rawurldecode($_SERVER["REQUEST_URI"]));
		}
		
		SOYShopUtil::resetShopMode($old);
		
		return $logoutPageUrl;
	}
	
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}
?>