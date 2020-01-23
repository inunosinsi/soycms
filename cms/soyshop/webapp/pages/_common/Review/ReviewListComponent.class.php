<?php

class ReviewListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addInput("review_check", array(
			"name" => "reviews[]",
			"value" => $entity->getId(),
			"onchange" => '$(\'#reviews_operation\').show();'
		));

		$this->addLabel("is_approved", array(
			"text" => ($entity->getIsApproved()) ? "許可" : "拒否"
		));

		$this->addLink("item_name", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $entity->getItemId()),
			"text" => ($entity instanceof SOYShop_ItemReview) ? soyshop_get_item_object($entity->getItemId())->getOpenItemName() : ""
		));

		$this->addModel("is_user_id", array(
			"visible" => ($entity->getUserId())
		));
		$this->addModel("no_user_id", array(
			"visible" => (is_null($entity->getUserId()))
		));

		$this->addLink("user_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $entity->getUserId())
		));

		$this->addLabel("user_name", array(
			"text" => $entity->getNickname()
		));

		$this->addLabel("evaluation", array(
			"html" => $entity->getEvaluationString()
		));

		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i", $entity->getCreateDate())
		));

		$this->addLabel("update_date", array(
			"text" => date("Y-m-d H:i", $entity->getUpdateDate())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Review.Detail." . $entity->getId())
		));
	}
}
