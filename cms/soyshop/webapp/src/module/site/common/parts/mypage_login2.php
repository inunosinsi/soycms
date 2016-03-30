<?php
function soyshop_parts_mypage_login2($html,$page){

	$obj = $page->create("soyshop_parts_mypage_login2", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_mypage_login2", $html)
	));

	if(!function_exists("soyshop_parts_mypage_login")){
		include(dirname(__FILE__) . "/mypage_login.php");
	}
	soyshop_parts_mypage_login($html, $page);
}
?>
