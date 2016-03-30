<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */
include_once(dirname(__FILE__) . "/common.php");
class ButtonSocialBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		
		//カート内の場合は動作しない
		$className = get_class($page);
		if($className == "SOYShop_CartPage" || $className == "SOYShop_UserPage") return;
		
		$common = new ButtonSocialCommon();
		
		$page->addLabel("og_meta", array(
			"soy2prefix" => "block",
			"html" => $common->getOgMeta($page->getPageObject())
		));
		
		$page->addLabel("facebook_meta", array(
			"soy2prefix" => "block",
			"html" => $common->getFbMeta()
		));
		
		$page->addLabel("facebook_like_button", array(
			"soy2prefix" => "block",
			"html" => $common->getFbButton()
		));
		
		$page->addLabel("twitter_button", array(
			"soy2prefix" => "block",
			"html" => $common->getTwitterButton()
		));
		
		$page->addLabel("hatena_button", array(
			"soy2prefix" => "block",
			"html" => $common->getHatenaButton()
		));

		$page->addLabel("mixi_check_button", array(
			"soy2prefix" => "block",
			"html" => $common->getMixiCheckButton()
		));

		$page->addLabel("mixi_like_button", array(
		  "soy2prefix" => "block",
 		  "html" => $common->getMixiLikeButton()
		));
		
		$page->addLabel("google_plus_button", array(
		  "soy2prefix" => "block",
 		  "html" => $common->getGooglePlusButton()
		));
	}	
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "button_social", "ButtonSocialBeforeOutput");
?>