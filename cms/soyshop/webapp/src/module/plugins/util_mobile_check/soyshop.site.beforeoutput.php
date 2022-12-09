<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class UtilMobileCheckBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		if(SOYSHOP_MOBILE_CARRIER == "PC" || (defined("SOYSHOP_DOCOMO_CSS") && SOYSHOP_DOCOMO_CSS == 0) ) return;

		if(!defined("SOYSHOP_MOBILE_CHARSET")){
			$charset = "Shift_JIS";
			switch(get_class($page)){
				case "SOYShop_CartPage":
					$charset = SOYShop_DataSets::get("config.cart.mobile_cart_charset", "Shift_JIS");
					break;
				case "SOYShop_UserPage":
					$charset = SOYShop_DataSets::get("config.mypage.mobile.charset", "Shift_JIS");
					break;
				default:
					$charset = $page->getPageObject()->getCharset();
					break;
			}
			define("SOYSHOP_MOBILE_CHARSET", $charset);
		}


		$header = "text/html;";

		if(SOYSHOP_IS_MOBILE){
			$carrier = SOYSHOP_MOBILE_CARRIER;

			if($carrier == "DoCoMo" || $carrier == "i-mode2.0"){
				$header = "application/xhtml+xml;";
			}
		}

		$header = $header." charset=" . $charset;
		header("Content-Type: " . $header);

		$page->addModel("mobile_http_equiv", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"attr:http-equiv" => "Content-Type",
			"attr:content" => $header
		));
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "util_mobile_check", "UtilMobileCheckBeforeOutput");
