<?php

class ItemReviewAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		SOY2::imports("module.plugins.item_review.domain.*");
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
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO")
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
