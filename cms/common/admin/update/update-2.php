<?php
$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");
try{
	$sites = $siteDAO->getBySiteType(Site::TYPE_SOY_CMS);
}catch(Exception $e){
	$sites = array();
}

$dsn = SOY2DAOConfig::Dsn();
foreach($sites as $site){
	SOY2DAOConfig::Dsn($site->getDataSourceName());
				
	$siteConfigDao = SOY2DAOFactory::create("cms.SiteConfigDAO");
	try{
		$siteConfig = $siteConfigDao->get();
	}catch(Exception $e){
		continue;
	}
	
	$siteConfig->setConfigValue("url", $site->getUrl());
	try{
		$siteConfigDao->updateSiteConfig($siteConfig);
	}catch(Exception $e){
		//
	}	
}
SOY2DAOConfig::Dsn($dsn);
?>