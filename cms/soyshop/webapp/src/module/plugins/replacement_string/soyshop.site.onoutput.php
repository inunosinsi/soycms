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

		foreach($list as $v){
			$html = str_replace($v["symbol"], $v["string"], $html);
		}

		return $html;
	}
}
SOYShopPlugin::extension("soyshop.site.onoutput", "replacement_string", "ReplacementStringOnOutput");
