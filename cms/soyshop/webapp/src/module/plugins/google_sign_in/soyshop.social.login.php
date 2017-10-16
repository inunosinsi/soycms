<?php
/*
 */
class GoogleSignInSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		$html = array();
		$html[] = "<div class=\"g-signin2\" data-onsuccess=\"onSignIn\"></div>";
		$html[] = "<script>\n" . file_get_contents(dirname(__FILE__) . "/js/sign.js") . "\n</script>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "google_sign_in", "GoogleSignInSocialLogin");
