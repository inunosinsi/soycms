<?php
/*
 */
class GoogleSignInSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
		$cnf = GoogleSignInUtil::getConfig();
		if(!isset($cnf["client_id"]) || is_bool(strpos($cnf["client_id"], "apps.googleusercontent.com"))) return "";

		$html = array();
		$html[] = GoogleSignInUtil::getButtonHTML($cnf["client_id"]);
		$html[] = "<script>\n" . file_get_contents(dirname(__FILE__) . "/js/sign.js") . "\n</script>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "google_sign_in", "GoogleSignInSocialLogin");
