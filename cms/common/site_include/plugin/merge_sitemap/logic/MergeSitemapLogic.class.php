<?php

class MergeSitemapLogic extends SOY2LogicBase{
	
	const XML_FILE_NAME = "merge.xml";
	
	function __construct(){}
	
	function getMergeXMLFilePath(){
		return self::getSiteDirectory() . self::XML_FILE_NAME;
	}
	
	private function getSiteDirectory(){
		if(defined("_SITE_ROOT_")){
			$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
			$siteRoot = _SITE_ROOT_ . "/";
		}else{
			$siteRoot = UserInfoUtil::getSiteDirectory();
			$siteId = UserInfoUtil::getSite()->getSiteId();
		}
		
		$old = CMSUtil::switchDsn();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		CMSUtil::resetDsn($old);
		
		if($site->getIsDomainRoot()){
			return str_replace("/" . $site->getSiteId(), "", $siteRoot);
		}else{
			return $siteRoot;
		}
	}
}
?>