<?php

class SOYInquiryConnectorUtil {

	public static function getSiteList(){
		$old = CMSUtil::switchDsn();
		try{
			$sites = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_CMS);
		}catch(Exception $e){
			$sites = array();
		}
		CMSUtil::resetDsn($old);
		if(!count($sites)) return array();

		$list = array();
		foreach($sites as $site){
			$list[(int)$site->getId()] = $site->getSiteName();
		}

		return $list;
	}

	public static function getInquiryPageList($siteId, $isTitle=false){
		$old = CMSUtil::switchDsn();
		try{
			$dsn = SOY2DAOFactory::create("admin.SiteDAO")->getById($siteId)->getDataSourceName();
		}catch(Exception $e){
			$dsn = null;
		}
		CMSUtil::resetDsn($old);
		if(is_null($dsn)) return array();

		$oldDsn = SOY2DAOConfig::dsn();
		SOY2DAOConfig::dsn($dsn);

		try{
			$pages = SOY2DAOFactory::create("cms.PageDAO")->getByPageType(Page::PAGE_TYPE_APPLICATION);
		}catch(Exception $e){
			$pages = array();
		}
		SOY2DAOConfig::dsn($oldDsn);
		if(!count($pages)) return array();

		$list = array();
		foreach($pages as $page){
			$obj = $page->getPageConfigObject();
			if(!property_exists($obj, "applicationId") || $obj->applicationId != "inquiry") continue;
			if($isTitle){
				$list[$page->getId()] = $page->getTitle() . " (/" . $page->getUri() . ")";
			}else{
				$list[$page->getId()] = $page->getUri();
			}
		}
		return $list;
	}
}
