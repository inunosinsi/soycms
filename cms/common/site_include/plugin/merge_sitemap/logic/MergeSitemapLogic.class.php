<?php

class MergeSitemapLogic extends SOY2LogicBase{
	
	const XML_FILE_NAME = "merge.xml";
	
	function __construct(){}
	
	function createMergeMap($urls){
		$xml = array();
			
			$xml[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
			$xml[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
			
			foreach($urls as $u){
				$x = @simplexml_load_string(file_get_contents(trim($u)));
				if(is_null($x) || $x === false) continue;
				foreach($x->url as $obj){
					$cols = array();
					$cols[] = "<url>";
					$cols[] = "	<loc>" . $obj->loc . "</loc>";
					$cols[] = "	<priority>" . $obj->priority . "</priority>";
					$cols[] = "	<lastmod>" . $obj->lastmod . "</lastmod>";
					$cols[] = "</url>";
					
					$xml[] = implode("\n", $cols);
				}
			}
			
			$xml[] = "</urlset>";
			
			file_put_contents(self::getMergeXMLFilePath(), implode("\n", $xml));
	}
	
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