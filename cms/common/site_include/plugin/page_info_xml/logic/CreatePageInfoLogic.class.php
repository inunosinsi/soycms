<?php

class CreatePageInfoLogic extends SOY2LogicBase{
	
	const XML_FILE_NAME = "pageinfo.xml";
	
	function __construct(){}
	
	function createPageInfoXml($urls, $removeStrings = array()){

		set_time_limit(0);

		$xml = array();
		
		$xml[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
		
		$i = 0;
		foreach($urls as $u){
			$x = @simplexml_load_string(file_get_contents(trim($u)));
			if(is_null($x) || $x === false) continue;
			foreach($x->url as $obj){
				//HTMLを取得する
				if(property_exists($obj, "loc")){
					$html = file_get_contents($obj->loc);
					if(preg_match('/<title>(.*)<\/title>/', $html, $res)){
						if(!isset($res[1]) || !strlen($res[1])) continue;
						
						$title = htmlspecialchars(trim($res[1]), ENT_QUOTES, "UTF-8");
						if(count($removeStrings)){
							foreach($removeStrings as $str){
								$title = trim(str_replace(trim($str), "", $title));
							}
						}
						
						$cols = array();
						$cols[] = "<url>";
						$cols[] = "	<loc>" . $obj->loc . "</loc>";
						$cols[] = "	<title>" . $title . "</title>";
						$cols[] = "</url>";
						
						$xml[] = implode("\n", $cols);
					}
				}
			}
		}
		
		$xml[] = "</urlset>";
		
		file_put_contents(self::getPageInfoXMLFilePath(), implode("\n", $xml));

	}
	
	function getPageInfoXMLFilePath(){
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