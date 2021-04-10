<?php
/*
 * Created on 2010/04/26
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//すでにセッションスタートしている場合のみ、PHPSESSIDを更新する スマホでは更新すべきではないらしい　PCでもうまくいかなかったことがあったので廃止
if(session_status() == PHP_SESSION_ACTIVE && USE_SESSION_REGENERATE_ID_MODE){
	SOY2ActionSession::regenerateSessionId();
}

SOY2::import("util.SOYShopPluginUtil");
include_once(dirname(__FILE__) . "/classes.php");
SOY2HTMLConfig::PageDir(dirname(dirname(__FILE__)). "/" . SOYSHOP_CURRENT_MYPAGE_ID . "/pages/");

//マイページのテンプレートの設定
$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/mypage/" . SOYSHOP_CURRENT_MYPAGE_ID . "/";
define("SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR", soy2_realpath(dirname(dirname(__FILE__))."/".SOYSHOP_CURRENT_MYPAGE_ID) . "pages/");
if(file_exists($templateDir)){
    define("SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR", $templateDir);
}else{
    define("SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR", SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR);
}

//マイページロジックの設定
MyPageLogic::getMyPage(SOYSHOP_CURRENT_MYPAGE_ID);

//最初はMYPAGE_IDの方を調べて、なければ_commonの方を調べる
$i = 0;
do{
	if(SOY2HTMLFactory::pageExists($htmlObj->createPagePath(true) . "Page")){
        //Hoge.IndexPage
        $path = $htmlObj->createPagePath(true) . "Page";
    }else{
		//HogePage
        $path = $htmlObj->createPagePath() . "Page";

        //MYPAGE_IDの方で無かったので、_commonの方を調べるように設定変更
        if(!SOY2HTMLFactory::pageExists($path)) {
			//alias付きであることを疑ってみる
			$values = explode(".", $htmlObj->createPagePath());
			$args[] = strtolower(array_pop($values));

			$values[] = ucfirst(array_pop($values));
			$path = implode(".", $values);
			if(!SOY2HTMLFactory::pageExists($path . "Page")){
				$path = strtolower($path) . ".Index";
				if(!SOY2HTMLFactory::pageExists($path . "Page")){
					SOY2HTMLConfig::PageDir(dirname(__FILE__).  "/pages/");
					continue;
				}
			}
			$path .= "Page";
			break;
		}
    }
}while($i++ < 1);

$path = MyPageLogic::convertPath($path);
define("SOYSHOP_MYPAGE_PATH", $path);

if(file_exists(SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR . str_replace(".", "/", SOYSHOP_MYPAGE_PATH) . ".html")){
	try{
		$page = SOY2HTMLFactory::createInstance($path, array("arguments" => $args));
	}catch(Exception $e){
		$page = SOY2HTMLFactory::createInstance("ErrorPage", array("arguments" => $args));
	}
//HTMLファイルがなければ必ずエラー
}else{
	$page = SOY2HTMLFactory::createInstance("ErrorPage", array("arguments" => $args));
}


$page->buildModules();
$page->display();
