<?php
/*
 * soyshop.site.onload.php
 * Created: 2010/02/20
 */

class ReplacementStringOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput(string $html){
		SOY2::import("module.plugins.replacement_string.util.ReplacementStringUtil");
		$list = ReplacementStringUtil::getConfig();
		if(!count($list)) return $html;

		$idx = (!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") ? "string" : SOYSHOP_PUBLISH_LANGUAGE;
		
		foreach($list as $v){
			$str = (isset($v[$idx])) ? $v[$idx] : "";
			$html = str_replace($v["symbol"], $str, $html);
		}

		return $html;
	}
}
SOYShopPlugin::extension("soyshop.site.onoutput", "replacement_string", "ReplacementStringOnOutput");
