<?php
/*
 */

class ButtonSocialCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
	}

	function getForm(SOYShop_Item $item){
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
						
		$htmlObj->addLabel("facebook_like_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $this->getFbButton($item)
		));
		
		$htmlObj->addLabel("twitter_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $this->getTwitterButton($item)
		));
		
		$htmlObj->addLink("twitter_button_mobile", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => $this->getTwitterButtonMobile($item)
		));
		
		$htmlObj->addLabel("hatena_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $this->getHatenaButton($item)
		));
		
		$htmlObj->addLabel("google_plus_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
 			"html" => $this->getGooglePlusButton()
		));
	}

	function onDelete($id){
	}
	
	function getFbButton(SOYShop_Item $item){				
		return "<div class=\"fb-like fb-like-comment\" data-href=\"" . $this->getPageUrl($item) . "\" data-send=\"false\" data-layout=\"button_count\" data-width=\"450\" data-show-faces=\"false\"></div>";
	}
	
	function getTwitterButton(SOYShop_Item $item){
		$url = $this->getPageUrl($item);
				
		return "<a href=\"http://twitter.com/share\" " .
				"class=\"twitter-share-button\" " .
				"data-url=\"" . $url."\" " .
				"data-count=\"horizontal\">Tweet</a>" .
				"<script type=\"text/javascript\" " .
				"src=\"http://platform.twitter.com/widgets.js\"></script>";
	}
	
	function getTwitterButtonMobile(SOYShop_Item $item){
		$url = rawurlencode($this->getPageUrl($item));
		$itemName = rawurlencode($item->getName());
		
		return "http://twtr.jp/share?url=" . $url . "&text=" . $itemName;
	}
	
	function getHatenaButton(SOYShop_Item $item){
		$url = $this->getPageUrl($item);
		
		return "<a href=\"http://b.hatena.ne.jp/entry/" . $url . "\" " .
				"class=\"hatena-bookmark-button\" " .
				"data-hatena-bookmark-layout=\"standard\" " .
				"title=\"このエントリーをはてなブックマークに追加\">" .
				"<img src=\"http://b.st-hatena.com/images/entry-button/button-only.gif\" " .
				"alt=\"このエントリーをはてなブックマークに追加\" " .
				"width=\"20\" height=\"20\" style=\"border: none;\" /></a>" .
				"<script type=\"text/javascript\" " .
				"src=\"http://b.st-hatena.com/js/bookmark_button.js\" charset=\"utf-8\" async=\"async\"></script>";
	}
	
	function getGooglePlusButton(){
		return "<div class=\"g-plusone\"></div>\n".
				"<script type=\"text/javascript\">\n".
				"  window.___gcfg = {lang: 'ja'};\n".
				"\n".
				"  (function() {\n".
				"    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;\n".
				"    po.src = 'https://apis.google.com/js/plusone.js';\n".
				"    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);\n".
				"  })();\n".
				"</script>";
	}
	
	private $pageDao;
	
	function getPageUrl(SOYShop_Item $item){
		if(!$this->pageDao){
			$this->pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		}
		
		try{
			$page = $this->pageDao->getById($item->getDetailPageId());
		}catch(Exception $e){
			$page = new SOYShop_Page();
		}
		
		$url = soyshop_get_site_url(true);
		
		$uri = $page->getUri();
		if(isset($uri)){
			$url = $url.$page->getUri() . "/" . $item->getAlias();
		}		
		
		return $url;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "button_social", "ButtonSocialCustomField");
?>