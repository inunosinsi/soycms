<?php

function register_item_review($stmt){
	SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
	$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$reviews = $dao->get();
			if(!count($reviews)) break;
		}catch(Exception $e){
			break;
		}

		foreach($reviews as $review){
			$stmt->execute(array(
				":id" => $review->getId(),
				":item_id" => $review->getItemId(),
				":user_id" => $review->getUserId(),
				":nickname" => $review->getNickname(),
				":title" => $review->getTitle(),
				":content" => $review->getContent(),
				":image" => $review->getImage(),
				":movie" => $review->getMovie(),
				":evaluation" => $review->getEvaluation(),
				":approval" => $review->getApproval(),
				":vote" => $review->getVote(),
				":attributes" => $review->getAttributes(),
				":is_approved" => $review->getIsApproved(),
				":create_date" => $review->getCreateDate(),
				":update_date" => $review->getUpdateDate()
			));
		}
	}
}
