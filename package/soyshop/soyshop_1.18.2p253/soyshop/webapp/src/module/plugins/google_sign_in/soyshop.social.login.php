<?php
/*
 */
class GoogleSignInSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		$html = array();
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
		$html[] = GoogleSignInUtil::getButtonHTML();
		$html[] = "<script>\n" . file_get_contents(dirname(__FILE__) . "/js/sign.js") . "\n</script>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "google_sign_in", "GoogleSignInSocialLogin");
