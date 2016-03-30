<?php
class ItemReviewOnOutput extends SOYShopSiteOnOutputAction{
	
	function onOutput($html){
		if(isset($_GET["captcha"])){

	    	header("Content-Type: image/jpeg");
			$captcha = str_replace(array(".", "/", "\\"), "", $_GET["captcha"]);
			echo file_get_contents(SOY2HTMLConfig::CacheDir() . $captcha . ".jpg");
			//CAPTCHA画像の削除
	    	@unlink(SOY2HTMLConfig::CacheDir() . $captcha . ".jpg");
		}
	}
}
SOYShopPlugin::extension("soyshop.site.onoutput", "item_review", "ItemReviewOnOutput");
?>