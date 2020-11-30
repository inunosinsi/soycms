<?php

class MypageReviewsListComponent extends HTMLList{

	private $config;

	protected function populateItem($entity){

		$config = $this->config;

		$item = soyshop_get_item_object($entity->getItemId());

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y年n月j日 H:i", $entity->getCreateDate()) : ""
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($entity->getUpdateDate())) ? date("Y年n月j日 H:i", $entity->getUpdateDate()) : ""
		));

		$this->addLink("item_link", array(
			"link" => soyshop_get_item_detail_link($item)
		));

		$this->addLabel("item_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("evaluation", array(
			"html" => $entity->getEvaluationString()
		));

		$this->addLabel("is_approved", array(
			"text" => ($entity->getIsApproved()) ? MessageManager::get("STATUS_ALLOW") : MessageManager::get("STATUS_REFUSE")
		));

		$this->addLink("detail_link", array(
			"link" => soyshop_get_mypage_url() . "/review/detail/" . $entity->getId(),
			"visible" => ($config["edit"])
		));

		$this->addActionLink("remove_link", array(
			"link" => soyshop_get_mypage_url() . "/review/remove/" . $entity->getId()
		));
	}

	function setConfig($config){
		$this->config = $config;
	}
}
