<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class GooleSignInUserOnOutput extends SOYShopSiteUserOnOutputAction{

	function onOutput(string $html){
		if(soy2_strpos($_SERVER["REQUEST_URI"], "/" . soyshop_get_mypage_uri() . "/login") < 0) return $html;

		/** ログインページのみ **/

		//</head>の上にclientIDを挿入する
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
		$cnf = GoogleSignInUtil::getConfig();
		if(!isset($cnf["client_id"]) || soy2_strpos($cnf["client_id"], "apps.googleusercontent.com") < 0) return $html;

		/**
		 * Google Sign-In for Websites → Sign In With Googleへ移行
		 * client_idはbodyの方に挿入する
		 */

		//$scopeTag = "<meta name=\"google-signin-scope\" content=\"profile email\">";
		//$metaClientId = "<meta name=\"google-signin-client_id\" content=\"" . htmlspecialchars($cnf["client_id"], ENT_QUOTES, "UTF-8") . "\">";
		// $jspath = "https://apis.google.com/js/platform.js";
		// if(isset($cnf["render_function"]) && strlen(trim($cnf["render_function"]))) $jspath .= "?onload=" . trim($cnf["render_function"]);
		// $scriptTag = "<script src=\"" . $jspath . "\" async defer></script>";
		// $insertTag = $scopeTag . "\n" . $metaClientId . "\n" . $scriptTag;

		$insertTag = "<script src=\"https://accounts.google.com/gsi/client\" async defer></script>";

		if(soy2_strpos($html, "</head>") >= 0){
			return str_replace("</head>", $insertTag . "\n</head>", $html);
		}else if(soy2_strpos($html, "</HEAD>") >= 0){
			return str_replace("</HEAD>", $insertTag . "\n</HEAD>", $html);
		}else{
			return $html;
		}
	}
}
SOYShopPlugin::extension("soyshop.site.user.onoutput", "google_sign_in", "GooleSignInUserOnOutput");
