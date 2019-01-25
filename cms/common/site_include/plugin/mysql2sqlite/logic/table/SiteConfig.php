<?php

function registerSiteConfig($stmt){
	try{
		$config = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
	}catch(Exception $e){
		return;
	}

	$stmt->execute(array(
		":name" => $config->getName(),
		":description" => $config->getDescription(),
		":siteConfig" => $config->getSiteConfig(),
		":charset" => $config->getCharset()
	));
}
