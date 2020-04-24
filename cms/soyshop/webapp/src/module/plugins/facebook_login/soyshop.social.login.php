<?php
/*
 */
class FacebookLoginSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		$html = array();
		$html[] = "<script>\n" . file_get_contents(dirname(__FILE__) . "/js/fb.js") . "</script>";
		$html[] = "<fb:login-button scope=\"public_profile,email\" onlogin=\"FacebookLoginPlugin.checkLoginState();\"></fb:login-button>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "facebook_login", "FacebookLoginSocialLogin");
