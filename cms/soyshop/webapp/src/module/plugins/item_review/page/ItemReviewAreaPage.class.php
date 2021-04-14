<?php

class ItemReviewAreaPage extends WebPage{

	function __construct(){}

	function execute(){
		parent::__construct();

		SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
		SOY2::imports("module.plugins.item_review.logic.*");

		$reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$reviewDao->setLimit(6);

		try{
			$reviews = $reviewDao->get();
		}catch(Exception $e){
			$reviews = array();
		}

		DisplayPlugin::toggle("more_reviews", (count($reviews) > 5));
		DisplayPlugin::toggle("has_reviews", (count($reviews) > 0));
		DisplayPlugin::toggle("no_reviews", (count($reviews) === 0));

		$reviews = array_slice($reviews, 0, 5);

		$this->createAdd("reviews_list", "_common.Review.ReviewListComponent", array(
			"list" => $reviews,
			"itemNameList" => SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getItemNameListByIds(self::_getItemIds($reviews))
		));
	}

	private function _getItemIds($reviews){
		if(!is_array($reviews) || !count($reviews)) return array();

		$ids = array();
		foreach($reviews as $review){
			if(is_numeric(array_search((int)$review->getItemId(), $ids))) continue;
			$ids[] = (int)$review->getItemId();
		}
		return $ids;
	}
}
