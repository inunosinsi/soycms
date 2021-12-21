<?php

class ItemReviewAreaPage extends WebPage{

	function __construct(){}

	function execute(){
		parent::__construct();

		$reviews = self::_get();

		$cnt = count($reviews);
		DisplayPlugin::toggle("more_reviews", ($cnt > 5));
		DisplayPlugin::toggle("has_reviews", ($cnt > 0));
		DisplayPlugin::toggle("no_reviews", ($cnt === 0));

		if($cnt > 6) $reviews = array_slice($reviews, 0, 5);

		$this->createAdd("reviews_list", "_common.Review.ReviewListComponent", array(
			"list" => $reviews,
			"itemNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getItemNameListByIds(self::_getItemIds($reviews)) : array()
		));
	}

	private function _get(){
		SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
		$reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$reviewDao->setLimit(6);

		try{
			return $reviewDao->get();
		}catch(Exception $e){
			return array();
		}
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
