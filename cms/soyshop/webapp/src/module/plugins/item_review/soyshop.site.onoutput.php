<?php
class ItemReviewOnOutput extends SOYShopSiteOnOutputAction{

	function onOutput(string $html){
		if(!isset($_GET["captcha"])) return null;

    	header("Content-Type: image/jpeg");
		$captcha = str_replace(array(".", "/", "\\"), "", $_GET["captcha"]);
		$cacheFile = SOY2HTMLConfig::CacheDir() . $captcha . ".jpg";
		if(file_exists($cacheFile)){
			echo file_get_contents($cacheFile);
			//CAPTCHA画像の削除
	    	@unlink($cacheFile);
		}
		return null;
	}
}
SOYShopPlugin::extension("soyshop.site.onoutput", "item_review", "ItemReviewOnOutput");
