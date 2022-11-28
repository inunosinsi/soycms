<?php
function soyshop_parts_login_form($html, $page){

	SOY2::import("util.SOYShopPluginUtil");

	$obj = $page->create("soyshop_parts_login_form", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_login_form", $html)
	));
	
	if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")){
		$mypageId = SOYShop_DataSets::get("config.mypage.id", "bryon");
		define("SOYSHOP_CURRENT_MYPAGE_ID", $mypageId);
	}
	
	$mypage = MyPageLogic::getMyPage();
	
	//ログインチェック
	$isLoggedIn = $mypage->getIsLoggedin();
	
	$obj->addModel("is_login", array(
		"visible" => ($isLoggedIn),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));
	
	$obj->addModel("no_login", array(
		"visible" => (!$isLoggedIn),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));
	
	//ログインリンク
	$loginPageUrl = soyshop_get_mypage_url() . "/login?r=" . rawurldecode($_SERVER["REQUEST_URI"]);
	$obj->addLink("login_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $loginPageUrl
	));
	
	$obj->addForm("login_form", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"action" => $loginPageUrl,
		"method" => "post"
	));
		
	$obj->addInput("login_id", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "text",
		"name" => "loginId",
		"value" => ""
	));
	
	//後方互換
	$obj->addInput("login_email", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "text",
		"name" => "loginId",
		"value" => ""
	));
		
	$obj->addInput("login_password", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "password",
		"name" => "password",
		"value" => ""
	));
	
	$obj->addInput("login_submit", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "submit", 
		"name" => "login"
	));
	
	$obj->addInput("auto_login", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "checkbox", 
		"name" => "login_memory"
	));
	
	$obj->display();
}
?>