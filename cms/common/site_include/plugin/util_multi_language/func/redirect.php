<?php
function multi_language_redirect(CMSPageController $controller, array $cnf, int $checkBrowserLanguage){
	// $_SERVER["REQUEST_URI"]にはGETパラメータでlanguageの値があるように見えるのに$_GET["language"]の値がない場合
	if(!isset($_GET["language"]) && (soy2_strpos($_SERVER["REQUEST_URI"], "?language=") > 0 || soy2_strpos($_SERVER["REQUEST_URI"], "&language="))){
		preg_match('/language=(.*)?/', $_SERVER["REQUEST_URI"], $tmp);
		if(isset($tmp[1]) && strlen($tmp[1]) === 2) {
			$_GET["language"] = $tmp[1];
			$_SERVER["QUERY_STRING"] = "language=".$tmp[1];
		}
	}

	$redirectLogic = SOY2Logic::createInstance("site_include.plugin.util_multi_language.logic.RedirectLanguageSiteLogic", array("config" => $cnf));

	//ブラウザの言語設定を確認するモード
	if($checkBrowserLanguage){
		$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

		$lngCnf = "";
		$lngList = SOYCMSUtilMultiLanguageUtil::allowLanguages();
		if(count($lngList)){
			foreach($lngList as $_lng => $_dust){
				if(preg_match('/^' . $_lng . '/i', $language)) {
					$lngCnf = $_lng;
					break;
				}
			}
		}

		//念の為
		if(!strlen($lngCnf)) $lngCnf = "jp";

	//言語切替ボタンを使うモード
	}else{
		$userSession = SOY2ActionSession::getUserSession();

		//言語切替ボタンを押したとき
		if(isset($_GET["language"])){
			$lngCnf = $redirectLogic->getLanguageArterCheck();
			$userSession->setAttribute("soycms_publish_language", $lngCnf);
			$userSession->setAttribute("soyshop_publish_language", $lngCnf);
		//押してないとき
		}else{
			$lngCnf = $userSession->getAttribute("soycms_publish_language");
			if(is_null($lngCnf)){
				//SOY Shopの方の言語設定も確認する
				$lngCnf = $userSession->getAttribute("soyshop_publish_language");

				if(is_null($lngCnf)){
					$lngCnf = "jp";
					$userSession->setAttribute("soycms_publish_language", $lngCnf);
				}
			}
		}
	}

	if(!defined("SOYCMS_PUBLISH_LANGUAGE")){
		define("SOYCMS_PUBLISH_LANGUAGE", $lngCnf);
		define("SOYSHOP_PUBLISH_LANGUAGE", $lngCnf);
	}
	$redirectPath = $redirectLogic->getRedirectPath();

	if($redirectLogic->checkRedirectPath($redirectPath)){
		// 応急処置
		if(!defined("SOYCMS_PHP_CGI_MODE")) define("SOYCMS_PHP_CGI_MODE", function_exists("php_sapi_name") && stripos(php_sapi_name(), "cgi") !== false );
		if(SOYCMS_PHP_CGI_MODE){	// ?pathinfo=***が自動で付与された時にリダイレクトがおかしくなる
			if(is_bool(strpos($_SERVER["REQUEST_URI"], "?pathinfo="))){
				SOY2PageController::redirect($redirectPath);
				exit;
			}

			/** GETパラメータでpathinfoがある時にリダイレクトを行うとリダイレクトループにハマる **/
			
			// 日本語設定以外の時はリダイレクトをしても良い
			if(SOYCMS_PUBLISH_LANGUAGE != SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP){
				SOY2PageController::redirect($redirectPath);
				exit;
			}

		}else{
			CMSPageController::redirect($redirectPath);
			exit;
		}
	}
}