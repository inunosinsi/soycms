<?php
/*
 */
class LoginWithAmazonSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		SOY2::import("module.plugins.login_with_amazon.util.LoginWithAmazonUtil");
		$cnf = LoginWithAmazonUtil::getConfig();
		if(!isset($cnf["client_id"]) || !strlen($cnf["client_id"])) return "";
		if(!isset($cnf["client_secret"]) || !strlen($cnf["client_secret"])) return "";

		$html = array();
		$html[] = "<a href id=\"LoginWithAmazon\">";
    	$html[] = "<img border=\"0\" alt=\"Login with Amazon\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/lwa/btnLWA_gold_156x32.png\" width=\"156\" height=\"32\" />";
 		$html[] = "</a>";
		$html[] = "<div id=\"amazon-root\"></div>";

		$js = file_get_contents(dirname(__FILE__) . "/js/sdk.js");
		$js = str_replace("##CLIENT_ID##", $cnf["client_id"], $js);

		//ログイン用URL
		$js = str_replace("##LOGIN_URL##", soyshop_get_mypage_url(true) . "?soyshop_download=login_with_amazon&login_with_amazon", $js);


		$html[] = "<script>\n" . $js . "\n</script>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "login_with_amazon", "LoginWithAmazonSocialLogin");
