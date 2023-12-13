<?php
class UtilMultiLanguageBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput(WebPage $page){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$uri = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "";
		if(is_numeric(strpos($uri, "?"))) $uri = substr($uri, 0, strpos($uri, "?"));
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			$page->addLink("language_" . $lang . "_link", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"link" => $uri."?language=" . $lang
			));
		}
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "util_multi_language", "UtilMultiLanguageBeforeOutput");
