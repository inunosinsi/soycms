<?php

//アプリケーションページに関する定数を定義する
function define_application_page_constant($uri){
	$isApp = false;
    $isCart = false;
    $isMypage = false;

    //多言語サイトプラグインをアクティブにしていないもしくはスマホページか日本語ページの時
    //もしくは携帯リダイレクトプラグインと多言語化サイトを同時に実行している場合
    if(
        (!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") ||
        (defined("SOYSHOP_PUBLISH_LANGUAGE") && (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE))
    ){
        if($uri == soyshop_get_cart_uri()){
            $isApp = true;
            $isCart = true;
        }else if($uri == soyshop_get_mypage_uri()){
            $isApp = true;
            $isMypage = true;
        }

    //多言語サイトプラグインをアクティブにしていて、多言語サイトを見ている時
    }else if(defined("SOYSHOP_PUBLISH_LANGUAGE")){
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		if(class_exists("UtilMultiLanguageUtil")){
			$config = UtilMultiLanguageUtil::getConfig();
			if(isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])){
				$cartUri = SOYShop_DataSets::get("config.cart.cart_url", "cart");
				$mypageUri = SOYShop_DataSets::get("config.mypage.url", "user");
				if($uri == $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $cartUri){
					$isApp = true;
					$isCart = true;
				}elseif($uri == $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $mypageUri){
					$isApp = true;
					$isMypage = true;
				}
			}
		}
    }

    define("SOYSHOP_APPLICATION_MODE", $isApp);
    define("SOYSHOP_CART_MODE", $isCart);
    define("SOYSHOP_MYPAGE_MODE", $isMypage);
}

function define_all_page_constant(){
	//カート・マイページ関連の定数
	if(!defined("SOYSHOP_CURRENT_CART_ID")) define("SOYSHOP_CURRENT_CART_ID", soyshop_get_cart_id());
	if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());

	//言語設定がされていない場合はここで日本語に設定する
	if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
}
