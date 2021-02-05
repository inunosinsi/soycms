<?php

class ConfigLogic extends SOY2LogicBase{

	function __construct(){}

	function getList(){
		$shops = self::_getShopList();

		$list = array();
		foreach($shops as $shop){
			$list[] = $shop->getSiteId();
		}

		return $list;
	}

	private function _getShopList(){
		$old = CMSUtil::switchDsn();

		try{
			$sites = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_SHOP);
		}catch(Exception $e){
			$sites = array();
		}

		CMSUtil::resetDsn($old);

		return $sites;
	}
}
