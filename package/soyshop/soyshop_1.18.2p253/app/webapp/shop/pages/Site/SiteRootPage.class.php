<?php

class SiteRootPage extends SOYShopWebPage{

    function __construct($args) {
    	$id = (int)$args[0];
    	
    	$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
    	try{
    		$shopSite = $dao->getById($id);
    	}catch(Exception $e){
    		$shopSite = new SOYShop_Site();
    	}
    	
    	$logic = SOY2Logic::createInstance("logic.ShopLogic");
    	$site = $logic->getSite($shopSite->getSiteId());
    	$res = $logic->updateDomainRootSite($site);
    	
    	if($res){
    		//再度値を取得する
	    	$site = $logic->getSite($site->getSiteId());
	    	
			//SOY2::RootDir()の書き換え
			$old = ShopUtil::switchConfig();
			ShopUtil::setShopSiteDsn($shopSite);
				
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