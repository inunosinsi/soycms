<?php
//端末に関するチェック
function define_check_access_device(){
	//モバイルは廃止
	if(!defined("SOYSHOP_IS_MOBILE")) define("SOYSHOP_IS_MOBILE", false);

	$agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? mb_strtolower($_SERVER['HTTP_USER_AGENT']) : "";

	//スマホ
	if(!defined("SOYSHOP_IS_SMARTPHONE")){	//念の為
		$isAccess = false;
		if(strlen($agent)){
			if(is_numeric(strpos($agent, "iphone"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "ipod"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "android")) && is_numeric(strpos($agent, "mobile"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "windows")) && is_numeric(strpos($agent, "phone"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "firefox")) && is_numeric(strpos($agent, "mobile"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "blackberry"))){
				$isAccess = true;
			}
		}
		define("SOYSHOP_IS_SMARTPHONE", $isAccess);
		if(SOYSHOP_IS_SMARTPHONE){	//タブレットのチェックの無駄を省くため
			if(!defined("SOYSHOP_IS_TABLET")) define("SOYSHOP_IS_TABLET", false);
		}
	}

	//タブレット
	if(!defined("SOYSHOP_IS_TABLET")){
		$isAccess = false;
		if(strlen($agent)){
			if(is_numeric(strpos($agent, "ipad"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "windows")) && is_numeric(strpos($agent, "touch"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "android")) && is_numeric(strpos($agent, "mobile"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "firefox")) && is_numeric(strpos($agent, "tablet"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "kindle")) || is_numeric(strpos($agent, "silk"))){
				$isAccess = true;
			}else if(is_numeric(strpos($agent, "playbook"))){
				$isAccess = true;
			}
		}
		define("SOYSHOP_IS_TABLET", $isAccess);
	}

	//session_regenerate_idを利用するか？ スマホやタブレットの場合はsession_regenerate_idを利用しない
	if(!defined("USE_SESSION_REGENERATE_ID_MODE")) {
		define("USE_SESSION_REGENERATE_ID_MODE", (!SOYSHOP_IS_SMARTPHONE && !SOYSHOP_IS_TABLET));
	}

	//念の為に残しておく
	$carrier = (USE_SESSION_REGENERATE_ID_MODE) ? "PC" : "";
	if(!defined("SOYSHOP_MOBILE_CARRIER")) define("SOYSHOP_MOBILE_CARRIER", $carrier);
}

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
