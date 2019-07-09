<?php

class GravatarPageLogic extends SOY2LogicBase {

	function __construct(){}

	function getPageUrl($pageId){
		static $url;
		if(is_null($url)){
			$url = self::__getSiteUrl();
			try{
				$url .= SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getUri() . "/";
			}catch(Exception $e){
				return $url;
			}
		}

		return $url;
	}

	function getSiteUrl(){
		return self::__getSiteUrl();
	}

	private function __getSiteUrl(){
		static $url;
		if(is_null($url)){
			if(defined("_SITE_ROOT_")){
				$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
			}else{
				$siteId = UserInfoUtil::getSite()->getSiteId();
			}

			$old = CMSUtil::switchDsn();
			try{
				$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
			}catch(Exception $e){
				$site = new Site();
			}
			CMSUtil::resetDsn($old);
			$url = "/";
			if(!$site->getIsDomainRoot()) $url .= $site->getSiteId() . "/";
		}

		return $url;
	}
}
