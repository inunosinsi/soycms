<?php

SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
class ItemReviewSitemapUtil {

	public static function getReviewPageId(){
		return self::_getReviewPageId();
	}

	//1ページあたり何個レビューを表示するか？
	public static function getDivideReviewCount(){
		$cnf = self::_config();
		return (isset($cnf["review_count"]) && is_numeric($cnf["review_count"]) && $cnf["review_count"] > 0) ? (int)$cnf["review_count"] : null;
	}

	public static function checkReviewPageId(int $pageId){
		$reviewPageId = self::_getReviewPageId();
		if($reviewPageId == $pageId) return true;

		// スマホページを開いている場合
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			$mbCnf = UtilMobileCheckUtil::getConfig();
			if(isset($mbCnf["prefix_i"]) && is_string($mbCnf["prefix_i"]) && strlen($mbCnf["prefix_i"])){
				return (self::_getMbReviewPageId($mbCnf["prefix_i"]) == $pageId);
			}
		}

		return false;
	}

	private static function _getReviewPageId(){
		static $id;
		if(is_null($id)){
			$cnf = self::_config();
			$id = (isset($cnf["review_page_id"]) && is_numeric($cnf["review_page_id"])) ? (int)$cnf["review_page_id"] : null;
		}
		return $id;
	}

	private static function _getMbReviewPageId(string $prefix){
		static $ids;
		if(isset($ids[$prefix])) return $ids[$prefix];

		$mbPageUri = $prefix . "/" . (string)soyshop_get_page_object(self::_getReviewPageId())->getUri();
		$ids[$prefix] = soyshop_get_page_object_by_uri($mbPageUri)->getId();
		return $ids[$prefix];
	}

	private static function _config(){
		static $cnf;
		if(is_null($cnf)) $cnf = ItemReviewUtil::getConfig();
		return $cnf;
	}
}
