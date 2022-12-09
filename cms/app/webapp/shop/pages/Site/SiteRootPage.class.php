<?php

class SiteRootPage extends SOYShopWebPage{

    function __construct($args) {
    	$id = (int)$args[0];
		$site = ShopUtil::getSiteById($id);

    	$logic = SOY2Logic::createInstance("logic.RootLogic");
    	$res = $logic->updateDomainRootSite($site);

    	if($res){
    		//再度値を取得する
	    	$site = ShopUtil::getSiteById($id);

			//SOY2::RootDir()の書き換え
			$old = ShopUtil::switchConfig();
			ShopUtil::setShopSiteDsn($site);

			try{
				$config = SOYShop_ShopConfig::load();
				$config->setSiteUrl(ShopUtil::getSiteUrl($site));
				SOYShop_ShopConfig::save($config);
				$res = true;
			}catch(Exception $e){
				$res = false;
			}
    	}

    	if($res){
			CMSApplication::jump("Site.Detail.".$id."?success");
		}else{
			CMSApplication::jump("Site.Detail.".$id."?error");
		}
    }
}
?>
