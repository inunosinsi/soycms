<?php

class ConfigLogic extends SOY2LogicBase{
	
	function ConfigLogic(){}
	
	function getList(){
		$shops = $this->getShopList();
		
		$list = array();
		
		foreach($shops as $shop){
			$list[] = $shop->getSiteId();
		}
		
		return $list;
	}
	
	function getShopList(){
		
		$old = CMSUtil::switchDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		
		try{
			$sites = $dao->getBySiteType(Site::TYPE_SOY_SHOP);
		}catch(Exception $e){
			$sites = array();
		}
		
		CMSUtil::resetDsn($old);
		
		return $sites;
	}
	
}