<?php
if(!class_exists("SOYShop_Campaign")){
	SOY2::imports("module.plugins.campaign.domain.*");
}
class CampaignListComponent extends HTMLList{

	function populateItem($entity, $key, $index){

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=campaign&mode=entry&id=" . $entity->getId())
		));

		//終了かどうか
		$status = ($entity->getPostPeriodEnd() < time()) ? " (終了)" : "";
		$start = (is_numeric($entity->getPostPeriodStart())) ? (int)$entity->getPostPeriodStart() : 0;
		$end = (is_numeric($entity->getPostPeriodEnd())) ? (int)$entity->getPostPeriodEnd() : 0;
		$this->addLabel("post_period", array(
			"text" => soyshop_convert_date_string($start) . " 〜 " . soyshop_convert_date_string($end) . $status
		));

		$this->addLabel("is_open", array(
			"text" => ($entity->getIsOpen() == SOYShop_Campaign::IS_OPEN) ? "公開" : "非公開"
		));

		$this->addLabel("title", array(
			"text" => $entity->getTitle()
		));
	}
}
