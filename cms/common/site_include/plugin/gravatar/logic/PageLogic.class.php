<?php

class PageLogic extends SOY2LogicBase {

  function __construct(){}

  function getPageUrl($pageId){
    static $url;
    if(is_null($url)){
      $url = self::getSiteUrl();
      try{
        $url .= SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getUri() . "/";
      }catch(Exception $e){
        return $url;
      }
    }

    return $url;
  }

  private function getSiteUrl(){
    static $url;
    if(is_null($url)){
      $siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
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
