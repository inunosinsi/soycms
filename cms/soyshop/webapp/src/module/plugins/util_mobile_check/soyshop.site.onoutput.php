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
		$config = UtilMobileCheckUtil::getConfig();
		$iPrefix = $config["prefix_i"];

		//PCの時のみ
		$requestUri = UtilMobileCheckUtil::getRequestUri();
		$convertUri = UtilMobileCheckUtil::removeCarrierPrefixUri($iPrefix);

		if($requestUri == $convertUri){
			if(isset($config["i_alternate"]) && $config["i_alternate"] == 1){
				$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
				$url = $http . "://" . $_SERVER["HTTP_HOST"] . UtilMobileCheckUtil::buildUrl($iPrefix);
				$alternateTag = "<link rel=\"alternate\" media=\"only screen and (max-width: 640px)\" href=\"" . $url . "\">";
				$html = str_ireplace('</head>', $alternateTag . "\n" . '</head>', $html);
			}
		}


		//この処理をprepareに移動
//		if(isset($_GET[session_name()])){
//			output_add_rewrite_var(session_name(), session_id());
//			return $html;

//			ob_list_handlers();
//			exit;
//		}
		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "util_mobile_check", "UtilMobileCheckOnOutput");
