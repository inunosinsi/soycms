<?php
class ButtonSocialCommon {
	
	private $shop;
	private $page;
	private $obj;
	
	function ButtonSocialCommon(){
		SOY2::import("module.plugins.button_social.util.ButtonSocialUtil");
	}
	
	function getOgMeta($page){
		$html = array();
				
		$this->shop = SOYShop_ShopConfig::load();
		$this->page = $page;
		$this->obj = $page->getObject();
		
		$config = ButtonSocialUtil::getConfig();
		
		$html[] = "<meta property=\"og:title\" content=\"" . $this->convertText($this->obj->getTitleFormat()) . "\" />";
		$html[] = "<meta property=\"og:site_name\" content=\"" . $this->shop->getShopName() . "\" />";
		$html[] = "<meta property=\"og:url\" content=\"" . $this->page->getCanonicalUrl() . "\" />";
		$html[] = "<meta property=\"og:type\" content=\"" . $this->getOgType() . "\" />";
		
		$imagePath = null;
		
		//詳細ページの場合、商品のサムネイルを優先
		if($page->getType() == SOYShop_Page::TYPE_DETAIL){
				$item = $page->getObject()->getCurrentItem();
				if(!is_null($item->getAttribute("image_small"))){
					$imagePath = soyshop_get_image_full_path($item->getAttribute("image_small"));
				}	
		}

		if(is_null($imagePath)){
			$imagePath = (isset($config["image"]) && strlen($config["image"]) > 0) ? $config["image"] : null;
		}
		
		if(isset($imagePath) && strlen($imagePath) > 0){
			$html[] = "<meta property=\"og:image\" content=\"" . $imagePath ."\" />";
		}
			
		$html[] = "<meta property=\"og:description\" content=\"" . $this->convertText($this->page->getDescription()) . "\" />";
		$html[] = "<meta property=\"og:locale\" content=\"ja_JP\" />";
		
		return implode("\n", $html);
	}
	
	function getFbMeta(){
		$html = array();
		
		$config = ButtonSocialUtil::getConfig();
		
		if(strlen($config["app_id"]) > 0 && strlen($config["admins"]) > 0){
			$html[] = "<meta property=\"fb:app_id\" content=\"" . $config["app_id"] . "\" />";
			$html[] = "<meta property=\"fb:admins\" content=\"" . $config["admins"] . "\" />";
		}
		return implode("\n", $html);
	}
	
	function getFbRoot(){
		
		$config = ButtonSocialUtil::getConfig();
		
		$html = array();
		
		if(strlen($config["app_id"]) > 0){
			$html[] = "<div id=\"fb-root\"></div>";
			$html[] = "<script>(function(d, s, id) {";
			$html[] = "	var js, fjs = d.getElementsByTagName(s)[0];";
			$html[] = "	if (d.getElementById(id)) return;";
			$html[] = "		js = d.createElement(s); js.id = id;";
			$html[] = "		js.src = \"//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=" . $config["app_id"] . "\";";
			$html[] = "		fjs.parentNode.insertBefore(js, fjs);";
			$html[] = "	}(document, 'script', 'facebook-jssdk'));";
			$html[] = "</script>";
		}
		
		return implode("\n", $html);
	}
	
	function getFbButton(){
		
		return "<div class=\"fb-like fb-like-comment\" data-href=\"" . $this->page->getCanonicalUrl() . "\" data-send=\"false\" data-layout=\"button_count\" data-width=\"450\" data-show-faces=\"false\"></div>";
	}
	
	function getTwitterButton(){
		
		return "<a href=\"http://twitter.com/share\" " .
				"class=\"twitter-share-button\" " .
				"data-url=\"" . $this->page->getCanonicalUrl() . "\" " .
				"data-count=\"horizontal\">Tweet</a>" .
				"<script type=\"text/javascript\" " .
				"src=\"http://platform.twitter.com/widgets.js\"></script>";
	}
	
	function getTwitterButtonMobile(){
		/**
		 * @ToDo
		 */
		$itemName = "商品名";
		$url = rawurlencode($this->page->getCanonicalUrl());
		$itemName = rawurlencode($itemName);
		
		return "http://twtr.jp/share?url=" . $url . "&text=" . $itemName;
	}
	
	function getHatenaButton(){
		return "<a href=\"http://b.hatena.ne.jp/entry/" . $this->page->getCanonicalUrl() . "\" " .
				"class=\"hatena-bookmark-button\" " .
				"data-hatena-bookmark-layout=\"standard\" " .
				"title=\"このエントリーをはてなブックマークに追加\">" .
				"<img src=\"http://b.st-hatena.com/images/entry-button/button-only.gif\" " .
				"alt=\"このエントリーをはてなブックマークに追加\" " .
				"width=\"20\" height=\"20\" style=\"border: none;\" /></a>" .
				"<script type=\"text/javascript\" " .
				"src=\"http://b.st-hatena.com/js/bookmark_button.js\" charset=\"utf-8\" async=\"async\"></script>";
	}

	function getMixiCheckButton(){
		$config = ButtonSocialUtil::getConfig();
		
		return "<a href=\"http://mixi.jp/share.pl\" " .
			"class=\"mixi-check-button\" " .
			"data-key=\"" . $config["check_key"] . "\" " .
			"data-url=\"" . $this->page->getCanonicalUrl() . "\" " .
			"data-button=\"button-6\">mixiチェック</a>" .
			"<script type=\"text/javascript\" " .
			"src=\"http://static.mixi.jp/js/share.js\"></script>";
	}

	function getMixiLikeButton(){
		$config = ButtonSocialUtil::getConfig();
		
		return "<div data-plugins-type=\"mixi-favorite\" " .
			"data-service-key=\"" . $config["check_key"] . "\" " .
			"data-href=\"" . $this->page->getCanonicalUrl() . "\" " .
			"data-show-faces=\"false\" " .
			"data-show-count=\"true\"></div>" . 
			"<script type=\"text/javascript\">(function(d) {var s = d.createElement('script'); s.type = 'text/javascript'; s.async = true;s.src = '//static.mixi.jp/js/plugins.js#lang=ja';d.getElementsByTagName('head')[0].appendChild(s);})(document);</script>";
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
		
	//文字列を置換して返す
	function convertText($format){
		
		//%SHOP_NAME%を置換する
		$format = str_replace("%SHOP_NAME%", $this->shop->getShopName(), $format);
		
		//%PAGE_NAME%を置換する
		$format = str_replace("%PAGE_NAME%", $this->page->getName(), $format);
		
		//残りの置換文字列を処理する
		$pageType = $this->page->getType();
		switch($pageType){
			case SOYShop_Page::TYPE_LIST:
				$current = $this->obj->getCurrentCategory();
				if(isset($current)) $format = str_replace("%CATEGORY_NAME%", $current->getName(), $format);
    			break;
    		case SOYShop_Page::TYPE_DETAIL:
    			$current = $this->obj->getCurrentItem();
    			if(isset($current)) $format = str_replace("%ITEM_NAME%", $current->getName(), $format);
    			break;
    		case SOYShop_Page::TYPE_SEARCH:
    			$q = (isset($_GET["q"])) ? $_GET["q"] : "";
    			$format = str_replace("%SEARCH_WORD%", $q, $format);
    			break;
		}
		 
		 return $format;
	}
	
	function getOgType(){
		$pageType = $this->page->getType();
		
		$ogType = "";		
		switch($pageType){
			case SOYShop_Page::TYPE_LIST:
				$ogType = "blog";
				break;
			case SOYShop_Page::TYPE_DETAIL:
				$ogType = "article";
				break;
			default:
				$ogType = "website";
				break;
		}
		
		return $ogType;
	}
}
?>