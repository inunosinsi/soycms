<?php
function soyshop_mypage_application(string $html, SOYShop_UserPage $htmlObj){
	if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
	
	$mypageId = $htmlObj->getMyPageId();
	$path = SOY2::RootDir() . "mypage/".$mypageId."/page.php";

	if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
		if(!file_exists($path)){
			$mypageId = str_replace("_".SOYSHOP_PUBLISH_LANGUAGE, "", $mypageId);
			$path = SOY2::RootDir() . "mypage/".$mypageId."/page.php";
		}
	}
	
	$args = $htmlObj->getPageArgs();
	
	ob_start();
	include($path);
	$html = ob_get_contents();
	ob_end_clean();

	echo $html;	
}
