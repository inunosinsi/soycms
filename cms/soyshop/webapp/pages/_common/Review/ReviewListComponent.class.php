<?php

class ReviewListComponent extends HTMLList{
	
	private $itemDao;
	private $logic;
	
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
			"text" => $this->getItemName($entity->getItemId())
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
		
		$this->addLabel("update_date", array(
			"text" => date("Y-m-d H:i", $entity->getUpdateDate())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Review.Detail." . $entity->getId())
		));
	}
		
	function getItemName($itemId){
		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		
		return $item->getName();
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>