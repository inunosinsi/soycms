<?php

class UtilMultiLanguagePrepareAction extends SOYShopSitePrepareAction{

	function prepare(){

		//既に設定している場合は処理を止める
		if(defined("SOYSHOP_PUBLISH_LANGUAGE")) return;

		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$config = UtilMultiLanguageUtil::getConfig();

		$redirectLogic = SOY2Logic::createInstance("module.plugins.util_multi_language.logic.RedirectLanguageSiteLogic");

		//ブラウザの言語設定を確認するモード
		if($config["check_browser_language_config"]){
			$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

			foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
				if(preg_match('/^' . $lang . '/i', $language)) {
					$languageConfig = $lang;
					break;
				}
			}

			//念の為
			if(!isset($languageConfig)) $languageConfig = "jp";

		//言語切替ボタンを使うモード
		}else{
			$userSession = SOY2ActionSession::getUserSession();

			//言語切替ボタンを押したとき
			if(isset($_GET["language"])){
				//切替設定があるか調べる
				$languageConfig = $redirectLogic->getLanguageArterCheck($config);
				$userSession->setAttribute("soyshop_publish_language", $languageConfig);
				$userSession->setAttribute("soycms_publish_language", $languageConfig);
			//押してないとき
			}else{
				$languageConfig = $userSession->getAttribute("soyshop_publish_language");
				if(is_null($languageConfig)){
					//SOY CMSの方の言語設定も確認する
					$languageConfig = $userSession->getAttribute("soycms_publish_language");

					if(is_null($languageConfig)){
						$languageConfig = "jp";
						$userSession->setAttribute("soyshop_publish_language", $languageConfig);
					}
				}
			}
		}

		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")){
			define("SOYCMS_PUBLISH_LANGUAGE", $languageConfig);
			define("SOYSHOP_PUBLISH_LANGUAGE", $languageConfig);
		}

		$redirectLogic->defineApplicationId($config);

		$redirectPath = $redirectLogic->getRedirectPath($config);

		if($redirectLogic->checkRedirectPath($redirectPath)){
			// 応急処置
			if(!defined("SOYCMS_PHP_CGI_MODE")) define("SOYCMS_PHP_CGI_MODE", function_exists("php_sapi_name") && stripos(php_sapi_name(), "cgi") !== false );
			if(SOYCMS_PHP_CGI_MODE){	// ?pathinfo=***が自動で付与された時にリダイレクトがおかしくなる
				if(is_bool(strpos($_SERVER["REQUEST_URI"], "?pathinfo="))){
					SOY2PageController::redirect($redirectPath);
					exit;
				}

				// GETパラメータでpathinfoがある時にリダイレクトを行うとリダイレクトループにハマる

			}else{
				SOY2PageController::redirect($redirectPath);
				exit;
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "util_multi_languare", "UtilMultiLanguagePrepareAction");
