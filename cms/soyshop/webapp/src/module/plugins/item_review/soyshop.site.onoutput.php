<?php
class ItemReviewOnOutput extends SOYShopSiteOnOutputAction{

	function onOutput($html){
		if(isset($_GET["captcha"])){

	    	header("Content-Type: image/jpeg");
			$captcha = str_replace(array(".", "/", "\\"), "", $_GET["captcha"]);
			$cacheFile = SOY2HTMLConfig::CacheDir() . $captcha . ".jpg";
			if(file_exists($cacheFile)){
				echo file_get_contents($cacheFile);
				//CAPTCHA画像の削除
		    	@unlink($cacheFile);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.onoutput", "item_review", "ItemReviewOnOutput");
