<?php

class SitemapXMLUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("sitemap_xml.config", array());
	}

	public static function saveConfig($values){
    	SOYShop_DataSets::put("sitemap_xml.config", $values);
	}

	public static function checkIsBodyTag(SOYShop_Page $page){
		$tmpFilePath = SOYSHOP_SITE_DIRECTORY . ".template/" . $page->getType() . "/" . $page->getTemplate();
		if(!file_exists($tmpFilePath)) return false;

		$html = file_get_contents($tmpFilePath);
		return (is_numeric(stripos($html, "<body")));
	}
}
