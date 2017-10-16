<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class GooleSignInUserOnOutput extends SOYShopSiteUserOnOutputAction{

	function onOutput($html){
		//ログインページのみ
		if(strpos($_SERVER["REQUEST_URI"], "/" . soyshop_get_mypage_uri() . "/login") !== false){
			//</head>の上にclientIDを挿入する
			$clientId = "539690015781-h0a2uqdjror7gmkvl9adi0cfl6n75ng1.apps.googleusercontent.com";
			$scopeTag = "<meta name=\"google-signin-scope\" content=\"profile email\">";
			$metaClientId = "<meta name=\"google-signin-client_id\" content=\"" . $clientId . "\">";
			$scriptTag = "<script src=\"https://apis.google.com/js/platform.js\" async defer></script>";
			$insertTag = $scopeTag . "\n" . $metaClientId . "\n" . $scriptTag;

			if(strpos($html, "</head>")){
				$html = str_replace("</head>", $insertTag . "\n</head>", $html);
			}else if(strpos($html, "</HEAD>")){
				$html = str_replace("</HEAD>", $insertTag . "\n</HEAD>", $html);
			}
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "google_sign_in", "GooleSignInUserOnOutput");
