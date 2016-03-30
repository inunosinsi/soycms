<?php

class SiteRootDetachPage extends SOYShopWebPage{

	function SiteRootDetachPage($args) {
		
		if(soy2_check_token()){
			$id = (isset($args[0])) ? (int)$args[0] : null;
			
			$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			try{
				$shopSite = $dao->getById($id);
			}catch(Exception $e){
				$shopSite = new SOYShop_Site();
			}
			
			$logic = SOY2Logic::createInstance("logic.ShopLogic");
			$site = $logic->getSite($shopSite->getSiteId());
			
			$res = $logic->detachDomainRootSite($site->getId());
			
			if($res){
				//再度値を取得する
				$site = $logic->getSite($site->getSiteId());
				
				if(!$site->getIsDomainRoot()){
					
					//SOY2::RootDir()の書き換え
					$old = ShopUtil::switchConfig();
					ShopUtil::setShopSiteDsn($shopSite);
					
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
?>