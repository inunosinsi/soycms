<?php

function register_review_point($stmt){
	SOY2::import("module.plugins.item_review.domain.SOYShop_ReviewPointDAO");
	$dao = SOY2DAOFactory::create("SOYShop_ReviewPointDAO");

	try{
		$points = $dao->get();
		if(!count($points)) return;
	}catch(Exception $e){
		return;
	}

	foreach($points as $point){
		$stmt->execute(array(
			":review_id" => $point->getReviewId(),
			":point" => $point->getPoint()
		));
	}
}
