<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class GoogleShoppingRssBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page") return;

		$title = $obj->getConvertedTitle();

		//SHOP_NAME
		$shopConfig = SOYShop_ShopConfig::load();
		$title = str_replace("%SHOP_NAME%", $shopConfig->getShopName(), $title);
		$title = str_replace("%PAGE_NAME%", $obj->getName(), $title);

		if($obj->getType() == SOYShop_Page::TYPE_LIST){

			if(strpos($_SERVER["REQUEST_URI"], "feed.xml")){

				$charset = (!is_null($obj->getCharset())) ? $obj->getCharset() : "UTF-8";

				$listPageObj = $obj->getPageObject();
				$pageType = $listPageObj->getType();

				switch($pageType){

					case SOYShop_ListPage::TYPE_CATEGORY:
						$items = $this->getItemsByCategory($listPageObj);
						break;
					case SOYShop_ListPage::TYPE_CUSTOM:
						$items = $this->getItemsByCustom($listPageObj);
						break;
					case SOYShop_ListPage::TYPE_FIELD:
						$items = $this->getItemsByField($listPageObj);
						break;
					default:
						$items = new SOYShop_Item();
						break;
				}

				header("Content-Type: application/rss+xml " . "charset=" . $charset);

				echo $this->soy_shop_item_list_output_rss($obj, $items, $title);
				exit;
			}

			$url = soyshop_get_site_url() . $obj->getUri();
			if(strrpos($url, "/") != strlen($url) - 1) $url = $url . "/";
			$url = $url . "feed.xml";

			$page->addModel("meta_google_rss", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"attr:rel" => "alternate",
				"attr:type" => "application/rss+xml",
				"attr:title" => $title,
				"attr:href" => $url
			));
		}
	}

	function getItemsByCategory($page){
		$categories = $page->getCategories();
		if(count($categories) == 0) $categories[] = $page->getDefaultCategory();

		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
			"sort" => $page
		));

		$config = $this->getRssConfig();
		$limit = $config["count"];

		try{
			$res = $logic->searchItems($categories, array(), array(), 0, $limit);
			$items = $res[0];
		}catch(Exception $e){
			$items = new SOYShop_Item();
		}

		return $items;
	}

	function getItemsByField($page){
		$res = array();

		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
			"sort" => $page
		));

		$config = $this->getRssConfig();
		$limit = $config["count"];

		$array = array(
			$page->getFieldId() => $page->getFieldValue()
		);

		list($res,$total) = $logic->searchByAttribute($array, 0, $limit);

		return $res;
	}

	function getItemsByCustom($obj){
		$res = array();

		$module = soyshop_get_plugin_object($obj->getModuleId());
		if(!is_null($module->getId())){
			SOYShopPlugin::load("soyshop.item.list", $module);
			$delegetor = SOYShopPlugin::invoke("soyshop.item.list", array(
				"mode" => "search"
			));

			$config = $this->getRssConfig();
			$limit = $config["count"];

			$res = $delegetor->getItems($obj, 0, $limit);
		}

		return $res;
	}

	function getRssConfig(){

		return SOYShop_DataSets::get("google_shopping_rss.config", array(
			"count" => "10"
		));
	}

	/**
	 * RSS2.0を出力
	 */
	function soy_shop_item_list_output_rss($page, $items, $title = null){
		function soy_shop_item_list_output_rss_h($string){
			return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
		}
		function soy_shop_item_list_output_rss_cdata($html){
			//タグを除去してエンティティを戻す
			$text = SOY2HTML::ToText($html);
			// ]]> があったらそこで分割する
			$cdata = "<![CDATA[" . str_replace("]]>", "]]]]><![CDATA[>", $text) ."]]>";
			return $cdata;
		}

		if(is_null($title)) $title = $page->getName();

		$xml = array();

		$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml[] = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		$xml[] = '<channel>';
		$xml[] = '<title>' . soy_shop_item_list_output_rss_h($title) . '</title>';
		$xml[] = '<link>' . soyshop_get_site_url(true) . $page->getUri() . "/" . '</link>';
		$xml[] = '<description>'.soy_shop_item_list_output_rss_h($page->getDescription()) . '</description>';
//		$xml[] = '<generator>'.'SOY SHOP '.SOYSHOP_VERSION.'</generator>';

		foreach($items as $item){

			$itemConfig = $item->getConfigObject();

			$urls = SOYShop_DataSets::get("site.url_mapping", array());
			$url = "";
			if(isset($urls[$item->getDetailPageId()])){
				$url = $urls[$item->getDetailPageId()]["uri"];
			}else{
				foreach($urls as $array){
					if($array["type"] == SOYShop_Page::TYPE_DETAIL){
						$url = $array["uri"];
						break;
					}
				}
			}

			$description = (isset($itemConfig["description"])) ? $itemConfig["description"] : "";
			$smallImagePath = (isset($itemConfig["image_small"])) ? "http://" . $_SERVER["SERVER_NAME"] . $itemConfig["image_small"] : "";

			$xml[] = '<item>';
			$xml[] = '<g:id>' . $item->getId() . '</g:id>';
			$xml[] = '<title>' . soy_shop_item_list_output_rss_h($item->getName()) . '</title>';
			$xml[] = '<link>' . soyshop_get_page_url($url,$item->getAlias()) . '</link>';
			$xml[] = '<g:price>' . $item->getPrice() . '</g:price>';
			$xml[] = '<description>' . soy_shop_item_list_output_rss_cdata($description) . '</description>';
			$xml[] = '<g:image_link>' . $smallImagePath . '</g:image_link>';
			$xml[] = '<g:condition>' . "new" . '</g:condition>';
			$xml[] = '</item>';
		}

		$xml[] = '</channel>';
		$xml[] = '</rss>';

		return implode("\n", $xml);
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "google_shopping_rss", "GoogleShoppingRssBeforeOutput");
