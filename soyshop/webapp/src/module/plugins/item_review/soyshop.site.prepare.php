<?php

class ItemReviewPrepare extends SOYShopSitePrepareAction{

	function prepare(){
		if(isset($_GET["captcha"])){
			$captcha = str_replace(array(".", "/", "\\"), "", $_GET["captcha"]);
			SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
			$cacheFile = ItemReviewUtil::cacheDir().$captcha.".jpg";
			if(file_exists($cacheFile) && is_readable($cacheFile)){
				header("Content-Type: image/jpeg");
				readfile($cacheFile);
				//CAPTCHA画像の削除
		    	@unlink($cacheFile);
				exit;
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "item_review", "ItemReviewPrepare");
