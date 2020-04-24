<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class FacebookCatalogManagerBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		$pageObj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($pageObj) || get_class($pageObj) != "SOYShop_Page") return;

		//sitemap.xmlでない場合は読み込まない
		if(!preg_match('/facebook_catalog_manager.xml/', $pageObj->getUri())) return;

		//フリーページ以外では読み込まない
		$pageType = $pageObj->getType();
		if($pageType != SOYShop_Page::TYPE_FREE) return;

		SOY2::import("module.plugins.facebook_catalog_manager.util.FbCatalogManagerUtil");
		$cnf = FbCatalogManagerUtil::getConfig();

		header("Content-Type: text/xml");

		$xml = array();
		$xml[] = "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">";
		$xml[] = "<channel>";
		$xml[] = "<title>" . $cnf["shopName"] . "</title>";
		$xml[] = "<link>" . soyshop_get_site_url(true) . "</link>";
		$xml[] = "<description>" . $cnf["shopDescription"] . "</description>";

		//高速化の為にここでカスタムフィールドの値を取得しておく
		$customs = FbCatalogManagerUtil::getExhibitionItemInfoList();

		if(count($customs)){
			foreach($customs as $itemId => $custom){
				$xml[] = self::_buildItem($itemId, $custom);
			}
		}


		$xml[] = "</channel>";
		$xml[] = "</rss>";

		$page->addLabel("facebook_catalog_manager.xml", array(
			"html" => implode("\n", $xml),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	private function _buildItem($itemId, $custom){
		$item = soyshop_get_item_object($itemId);

		$info = (isset($custom[FbCatalogManagerUtil::FIELD_ID_ITEM_INFO])) ? $custom[FbCatalogManagerUtil::FIELD_ID_ITEM_INFO] : array();

		$xml = array();
		$xml[] = "<item>";
		$xml[] = "<g:id>" . $item->getCode() . "</g:id>";
		$xml[] = "<g:title>" . $item->getName() . "</g:title>";
		if(isset($info["shopDescription"]) && strlen($info["shopDescription"])){
			$xml[] = "<g:description>" . $info["shopDescription"] . "</g:description>";
		}
		$xml[] = "<g:link>" . soyshop_get_item_detail_link($item) . "</g:link>";

		if(isset($info["image"]) && strlen($info["image"])){
			$xml[] = "<g:image_link>" . soyshop_convert_file_path($info["image"], $item, true) . "</g:image_link>";
		}

		if(isset($info["brand"]) && strlen($info["brand"])){
			$xml[] = "<g:brand>" . $info["brand"] . "</g:brand>";
		}
		$xml[] = "<g:condition>" . $info["condition"] . "</g:condition>";

		$xml[] = "<g:availability>" . self::_checkOrderable($item) . "</g:availability>";
		$xml[] = "<g:inventory>" . $item->getOpenStock() . "</g:inventory>";
		$xml[] = "<g:price>" . $item->getPrice() . " JPY</g:price>";

		//配送料
		if(isset($info["shippingPrice"]) && is_numeric($info["shippingPrice"])){
			$xml[] = "<g:shipping>";
			$xml[] = "<g:country>JP</g:country>";
			$xml[] = "<g:service>Standard</g:service>";
			$xml[] = "<g:price>" . $info["shippingPrice"] . " JPY</g:price>";
			$xml[] = "</g:shipping>";
		}

		$taxonomy = (isset($custom[FbCatalogManagerUtil::FIELD_ID_TAXONOMY])) ? $custom[FbCatalogManagerUtil::FIELD_ID_TAXONOMY] : array();
		if(count($taxonomy)){
			$xml[] = "<g:google_product_category>" . implode(" &gt; ", $taxonomy) . "</g:google_product_category>";
		}
		$xml[] = "</item>";

		return implode("\n", $xml);
	}

	//@ToDo 在庫管理はいずれ詳細設定できるようにしたい
	private function _checkOrderable(SOYShop_Item $item){
		if($item->getOpenStock() === 0) return "out of stock";

		$now = time();
		if($item->getOrderPeriodStart() > $now) return "preorder";
		if($item->getOrderPeriodEnd() < $now) return "discontinued";

		return "in stock";
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "facebook_catalog_manager", "FacebookCatalogManagerBeforeOutput");
