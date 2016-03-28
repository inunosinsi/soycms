<?php
$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");
try{
	$sites = $siteDAO->getBySiteType(Site::TYPE_SOY_CMS);
}catch(Exception $e){
	$sites = array();
}

foreach($sites as $site){
	if($site->getIsDomainRoot()){
		$defaultUrl = UserInfoUtil::getSiteURLBySiteId($site->getSiteId());
		
		//ルート設定をしていて、サイトURLを変更していない場合はルート設定前のURLをサイトURLに入れて更新する
		if(strpos($defaultUrl, $site->getUrl()) !== false){
			$site->setUrl($defaultUrl);
			
			try{
				$siteDAO->update($site);
			}catch(Exception $e){
				//
			}
		}
	}	
}

//念の為に各サイト毎のSiteConfigにurlを放り込んでおく
$dsn = SOY2DAOConfig::Dsn();
foreach($sites as $site){
	SOY2DAOConfig::Dsn($site->getDataSourceName());
	$siteConfigDao = SOY2DAOFactory::create("cms.SiteConfigDAO");
	try{
		$siteConfig = $siteConfigDao->get();
	}catch(Exception $e){
		continue;
	}
	
	$defaultUrl = UserInfoUtil::getSiteURLBySiteId($site->getSiteId());
	if($site->getIsDomainRoot() && strpos($defaultUrl, $site->getUrl()) !== false){
		$siteUrl = UserInfoUtil::getSiteURLBySiteId("");
	}else{
		$siteUrl = $site->getUrl();
	}
	
	$siteConfig->setConfigValue("url", $siteUrl);
	try{
		$siteConfigDao->updateSiteConfig($siteConfig);
	}catch(Exception $e){
		//
	}
}
SOY2DAOConfig::Dsn($dsn);
?>