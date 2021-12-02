<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */
class TwitterProductCardsBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

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

		list($label, $value) = $this->getFieldValues($item);
		$smallImagePath = $item->getAttribute("image_small");
		$thumbnailPath = (is_string($smallImagePath)) ? soyshop_get_image_full_path($smallImagePath) : "";

		$html = array();
		$html[] = "<meta name=\"twitter:card\" content=\"product\">";
		$html[] = "<meta name=\"twitter:url\" content=\"" . soyshop_get_item_detail_link($item) . "\">";
		$html[] = "<meta name=\"twitter:domain\" content=\"" . $_SERVER["HTTP_HOST"] . "\">";
		$html[] = "<meta name=\"twitter:site\" content=\"@" . $config["site"] . "\">";
		$html[] = "<meta name=\"twitter:creator\" content=\"@" . $config["creater"] . "\">";
		$html[] = "<meta name=\"twitter:title\" content=\"" . $item->getName() . "\">";
		$html[] = "<meta name=\"twitter:description\" content=\"" . $item->getAttribute("description") . "\">";
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

	function getFieldValues(SOYShop_Item $item){
		try{
			$v = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->get($item->getId(), "twitter_product_cards")->getValue();
		}catch(Exception $e){
			return array("", "");
		}
		$values = (is_string($v) && strlen($v)) ? soy2_unserialize($v) : array();
		$label = (isset($values["label"])) ? $values["label"] : "";
		$value = (isset($values["value"])) ? $values["value"] : "";

		return array($label, $value);
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "twitter_product_cards", "TwitterProductCardsBeforeOutput");
