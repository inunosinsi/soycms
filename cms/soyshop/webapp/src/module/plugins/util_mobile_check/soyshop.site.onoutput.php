<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class UtilMobileCheckOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
		$cnf = UtilMobileCheckUtil::getConfig();
		$iPrefix = $cnf["prefix_i"];

		//PCの時のみ
		if(UtilMobileCheckUtil::getRequestUri() == UtilMobileCheckUtil::removeCarrierPrefixUri($iPrefix)){
			if(isset($cnf["i_alternate"]) && $cnf["i_alternate"] == 1){
				$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
				$url = $http . "://" . $_SERVER["HTTP_HOST"] . UtilMobileCheckUtil::buildUrl($iPrefix);
				$alternateTag = "<link rel=\"alternate\" media=\"only screen and (max-width: 640px)\" href=\"" . $url . "\">";
				$html = str_ireplace('</head>', $alternateTag . "\n" . '</head>', $html);
			}
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "util_mobile_check", "UtilMobileCheckOnOutput");
