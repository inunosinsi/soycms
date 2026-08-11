<?php

class ReviewListComponent extends HTMLList{

	private $itemNameList = array();

	protected function populateItem($entity){

		$this->addInput("review_check", array(
			"name" => "reviews[]",
			"value" => $entity->getId(),
			"onchange" => '$(\'#reviews_operation\').show();'
		));

		$this->addLabel("is_approved", array(
			"text" => ($entity->getIsApproved()) ? "許可" : "拒否"
		));

		$itemId = (is_numeric($entity->getItemId())) ? (int)$entity->getItemId() : 0;
		$this->addLink("item_name", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $itemId),
			"text" => ($entity instanceof SOYShop_ItemReview && isset($this->itemNameList[$itemId])) ? $this->itemNameList[$itemId] : ""
		));

		$userId = (is_numeric($entity->getUserId())) ? (int)$entity->getUserId() : 0;
		$this->addModel("is_user_id", array(
			"visible" => ($userId > 0)
		));
		$this->addModel("no_user_id", array(
			"visible" => ($userId === 0)
		));

		$this->addLink("user_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $userId)
		));

		$this->addLabel("user_name", array(
			"text" => $entity->getNickname()
		));

		$this->addLabel("evaluation", array(
			"html" => $entity->getEvaluationString()
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i", $entity->getCreateDate()) : ""
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($entity->getUpdateDate())) ? date("Y-m-d H:i", $entity->getUpdateDate()) : ""
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Review.Detail." . $entity->getId())
		));
	}

	function setItemNameList($itemNameList){
		$this->itemNameList = $itemNameList;
	}
}
