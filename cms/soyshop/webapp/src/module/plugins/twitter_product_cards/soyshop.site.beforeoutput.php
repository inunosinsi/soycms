<?php

class TwitterProductCardsBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput(WebPage $page){

		//カート内の場合は動作しない
		$className = get_class($page);
		if($className == "SOYShop_CartPage" || $className == "SOYShop_UserPage") return;

		//詳細ページでのみ動作します
		if($page->getPageObject()->getType() !== SOYShop_Page::TYPE_DETAIL) return;

		$page->addLabel("twitter_product_cards_meta", array(
			"soy2prefix" => "block",
			"html" => $this->getMetaTag($page->getItem())
		));
	}

	function getMetaTag(SOYShop_Item $item){
		include_once(dirname(__FILE__) . "/common.php");
		$config = TwitterProductCardsCommon::getConfig();

		list($label, $value) = self::_getFieldValues($item);
		$thumbnailPath = soyshop_get_image_full_path((string)$item->getAttribute("image_small"));

		$html = array();
		$html[] = "<meta name=\"twitter:card\" content=\"product\">";
		$html[] = "<meta name=\"twitter:url\" content=\"" . soyshop_get_item_detail_link($item) . "\">";
		$html[] = "<meta name=\"twitter:domain\" content=\"" . $_SERVER["HTTP_HOST"] . "\">";
		$html[] = "<meta name=\"twitter:site\" content=\"@" . $config["site"] . "\">";
		$html[] = "<meta name=\"twitter:creator\" content=\"@" . $config["creater"] . "\">";
		$html[] = "<meta name=\"twitter:title\" content=\"" . $item->getName() . "\">";
		$html[] = "<meta name=\"twitter:description\" content=\"" . (string)$item->getAttribute("description") . "\">";
		$html[] = "<meta name=\"twitter:image\" content=\"" . $thumbnailPath . "\">";
		$html[] = "<meta name=\"twitter:data1\" content=\"" . $item->getSellingPrice() . "円\">";
		$html[] = "<meta name=\"twitter:label1\" content=\"Price\">";

		if(strlen($label) > 0 && strlen($value)){
			$html[] = "<meta name=\"twitter:data2\" content=\"" . $value . "\">";
			$html[] = "<meta name=\"twitter:label2\" content=\"" . $label . "\">";
		}

		$html[] = "<!-- additional footer tags available (See the App Installs and Deep Linking document to learn more) -->";

		return implode("\n", $html);
	}

	private function _getFieldValues(SOYShop_Item $item){
		$values = soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), "twitter_product_cards", "string"));
		$label = (isset($values["label"])) ? $values["label"] : "";
		$value = (isset($values["value"])) ? $values["value"] : "";

		return array($label, $value);
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "twitter_product_cards", "TwitterProductCardsBeforeOutput");
