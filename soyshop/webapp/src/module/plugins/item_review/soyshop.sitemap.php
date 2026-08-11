<?php

class ItemReviewSitemap extends SOYShopSitemapBase{

	function __construct(){
		SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
		SOY2::import("module.plugins.item_review.util.ItemReviewSitemapUtil");
	}

	function items(){
		$reviews = self::_dao()->getReviewCountListEachItems();
		if(!count($reviews)) return array();

		$uri = soyshop_get_page_object(ItemReviewSitemapUtil::getReviewPageId())->getUri();
		$items = array();
		$lastmods = array();

		$div = ItemReviewSitemapUtil::getDivideReviewCount();
		if($div > 0){
			foreach($reviews as $itemId => $cnt){
				$pageCnt = (int)max(1, ceil($cnt / $div));
				if($pageCnt > 0){
					for($pi = 1; $pi <= $pageCnt; $pi++){
						//最終投稿日
						if(!isset($lastmods[$itemId])) $lastmods[$itemId] = self::_dao()->getLastReviewDate($itemId);
						$items[] = array("loc" => $uri . "/" . $itemId . "/page-" . $pi . ".html", "priority" => "0.5", "lastmod" => $lastmods[$itemId]);
					}
				}
			}
		}
		return $items;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.sitemap", "item_review", "ItemReviewSitemap");
