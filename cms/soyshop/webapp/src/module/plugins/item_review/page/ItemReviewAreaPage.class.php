<?php

class ItemReviewAreaPage extends WebPage{

	function __construct(){}

	function execute(){
		parent::__construct();

		$reviews = self::_get();

		$cfgs = soyshop_get_arrival_config("item_review");

		$cnt = count($reviews);
		DisplayPlugin::toggle("more_reviews", ($cnt > $cfgs["count"]));
		DisplayPlugin::toggle("has_reviews", ($cnt > 0));
		DisplayPlugin::toggle("no_reviews", ($cnt === 0));

		if($cnt > $cfgs["count"]) $reviews = array_slice($reviews, 0, $cfgs["count"]);

		$this->createAdd("reviews_list", "_common.Review.ReviewListComponent", array(
			"list" => $reviews,
			"itemNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getItemNameListByIds(self::_getItemIds($reviews)) : array()
		));

		$this->addLabel("display_count", array(
			"text" => $cfgs["count"]	
		));
	}

	private function _get(){
		SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
		$reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		
		$cfgs = soyshop_get_arrival_config("item_review");

		try{
			$res =  $reviewDao->executeQuery(
				"SELECT item_id, user_id, nickname, evaluation, is_approved, create_date FROM soyshop_item_review ".
				"WHERE create_date > :date ".
				"ORDER BY create_date DESC ".
				"LIMIT ".$cfgs["count"]+1,
				array(":date" => strtotime("-".$cfgs["period"]."month"))
			);
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = $reviewDao->getObject($v);
		}
		
		return $_arr;
	}

	private function _getItemIds(array $reviews){
		if(!count($reviews)) return array();

		$ids = array();
		foreach($reviews as $review){
			if(is_numeric(array_search((int)$review->getItemId(), $ids))) continue;
			$ids[] = (int)$review->getItemId();
		}
		return $ids;
	}
}
