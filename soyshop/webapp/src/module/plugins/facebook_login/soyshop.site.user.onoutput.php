<?php

class FacebookLoginUserOnOutput extends SOYShopSiteUserOnOutputAction{

	function onOutput(string $html){
		if(soy2_strpos($_SERVER["REQUEST_URI"], "/" . soyshop_get_mypage_uri() . "/login") < 0) return $html;

		//ログインページのみ
		SOY2::import("module.plugins.facebook_login.util.FacebookLoginUtil");
		$cnf = FacebookLoginUtil::getConfig();
		if(!isset($cnf["app_id"]) || !isset($cnf["api_version"])) return $html;

		$appId = htmlspecialchars(trim($cnf["app_id"]), ENT_QUOTES, "UTF-8");
		$version = htmlspecialchars(trim($cnf["api_version"]), ENT_QUOTES, "UTF-8");

		if(soy2_stripos($html, '<body>') >= 0){
			return str_ireplace('<body>', '<body>' . "\n" . self::buildFbRoot($appId, $version), $html);
		}elseif(preg_match('/<body\\s[^>]+>/', $html)){
			return preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . self::buildFbRoot($appId, $version), $html);
		}else{
			return $html;
		}
	}

	private function buildFbRoot(string $appId, string $version){
		$html = array();
		$html[] = "<script>";
		$html[] = "window.fbAsyncInit = function() {";
		$html[] = "	FB.init({";
		$html[] = "		appId      : '" . $appId . "',";
     	$html[] = "		cookie     : true,";
		$html[] = "		xfbml      : true,";
		$html[] = "		version    : '" . $version . "'";
		$html[] = "	});";
    	$html[] = "	FB.AppEvents.logPageView();";
		$html[] = "};";
		$html[] = "";
		$html[] = "(function(d, s, id){";
		$html[] = "	var js, fjs = d.getElementsByTagName(s)[0];";
		$html[] = "	if (d.getElementById(id)) {return;}";
		$html[] = "	js = d.createElement(s); js.id = id;";
		$html[] = "	js.src = \"//connect.facebook.net/en_US/sdk.js\";";
		$html[] = "	fjs.parentNode.insertBefore(js, fjs);";
		$html[] = "}(document, 'script', 'facebook-jssdk'));";
		$html[] = "</script>";

		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "facebook_login", "FacebookLoginUserOnOutput");
