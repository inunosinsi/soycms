<?php
class GmailAPIOAuthPrepare extends SOYShopSitePrepareAction{

	function prepare(){
		SOY2::import("module.plugins.gmail_api_oauth.util.GmailApiOAuthUtil");
		if(!isset($_SERVER["REQUEST_URI"]) || is_bool(strpos($_SERVER["REQUEST_URI"], GmailApiOAuthUtil::CALLBACK_URI))) return;
		if(file_exists(GmailApiOAuthUtil::getClientSecretJsonPath())){
			include_once("oauth.php");
		}else{
			header("HTTP/1.1 404 Not Found");
			echo "<h1>404 Not Found</h1>";
			echo "お探しのページは見つかりませんでした。";
			exit;
		}
	}
}

SOYShopPlugin::extension("soyshop.site.prepare", "gmail_api_oauth", "GmailAPIOAuthPrepare");
