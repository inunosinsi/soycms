<?php

class SiteRootDetachPage extends SOYShopWebPage{

	function __construct($args) {

		if(soy2_check_token()){
			$id = (isset($args[0])) ? (int)$args[0] : null;

			$site = ShopUtil::getSiteById($id);

			$logic = SOY2Logic::createInstance("logic.RootLogic");
			$res = $logic->detachDomainRootSite($site->getId());

			if($res){
				//再度値を取得する
				$site = ShopUtil::getSiteById($site->getId());

				if(!$site->getIsDomainRoot()){

					//SOY2::RootDir()の書き換え
					$old = ShopUtil::switchConfig();
					ShopUtil::setShopSiteDsn($site);

					try{
						$config = SOYShop_ShopConfig::load();
						$config->setSiteUrl($site->getUrl());
						SOYShop_ShopConfig::save($config);
						$res = true;
					}catch(Exception $e){
						$res = false;
					}
				}
			}

			if($res){
				CMSApplication::jump("Site.Detail." . $id . "?detach");
			}else{
				CMSApplication::jump("Site.Detail." . $id . "?error");
			}
		}
	}
}
