<?php

function soyshop_cart_application(string $html, SOYShop_CartPage $htmlObj){
	if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

	$cartId = $htmlObj->getCartId();
	
	$path = SOY2::RootDir() . "cart/".$cartId."/page.php";
	if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
		if(!file_exists($path)){
			$cartId = str_replace("_".SOYSHOP_PUBLISH_LANGUAGE, "", $cartId);
			$path = SOY2::RootDir() . "cart/".$cartId."/page.php";
		}
	}

	ob_start();
	include($path);
	$html = ob_get_contents();
	ob_end_clean();

	echo $html;
}