<?php
/*
 * Created on 2010/04/26
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once(dirname(__FILE__) . "/classes.php");
SOY2HTMLConfig::PageDir(dirname(__FILE__).  "/pages/");

//マイページのテンプレートの設定
$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/mypage/" . SOYSHOP_CURRENT_MYPAGE_ID . "/";
define("SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR", soy2_realpath(dirname(dirname(__FILE__))."/".SOYSHOP_CURRENT_MYPAGE_ID) . "pages/");
if(file_exists($templateDir)){
	define("SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR", $templateDir);
}else{
	define("SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR", SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR);
}

//マイページロジックの設定
$myPage = MyPageLogic::getMyPage(SOYSHOP_CURRENT_MYPAGE_ID);
if(SOY2HTMLFactory::pageExists($htmlObj->createPagePath(true) . "Page")){
	//Hoge.IndexPage
	$path = $htmlObj->createPagePath(true) . "Page";
}else{
	//HogePage
	$path = $htmlObj->createPagePath() . "Page";
}
$path = MyPageLogic::convertPath($path);
define("SOYSHOP_MYPAGE_PATH", $path);

try{
	$page = SOY2HTMLFactory::createInstance($path, array("arguments" => $args));
}catch(Exception $e){
	$page = SOY2HTMLFactory::createInstance("ErrorPage", array("arguments" => $args));
}

$page->buildModules();
$page->display();

?>