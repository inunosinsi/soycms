<?php

function registerSiteConfig($stmt){
	$config = soycms_get_site_config_object();

	$stmt->execute(array(
		":name" => $config->getName(),
		":description" => $config->getDescription(),
		":siteConfig" => $config->getSiteConfig(),
		":charset" => $config->getCharset()
	));
}
