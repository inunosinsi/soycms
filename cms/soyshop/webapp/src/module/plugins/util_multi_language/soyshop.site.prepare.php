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
            $languageConfig = self::getAcceptLanguage();

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

                        //初回アクセスのみブラウザの言語設定をみる
                        if($config["check_first_access_config"]){
                            $languageConfig = self::getAcceptLanguage();
                        }else{
                            $languageConfig = "jp";
                        }
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
            SOY2PageController::redirect($redirectPath);
            exit;
        }
    }

    private function getAcceptLanguage(){
        $language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

        $languageConfig = null;
        foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
            if(preg_match('/^' . $lang . '/i', $language)) {
                $languageConfig = $lang;
                break;
            }
        }

        //念の為
        if(!isset($languageConfig)) $languageConfig = "jp";

        return $languageConfig;
    }
}
SOYShopPlugin::extension("soyshop.site.prepare", "util_multi_language", "UtilMultiLanguagePrepareAction");
