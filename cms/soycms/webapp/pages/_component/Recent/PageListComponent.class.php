<?php

class PageListComponent extends HTMLList{

	function populateItem($entity){

		$this->addLink("title", array(
			"text"=>(strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
			"link"=>SOY2PageController::createLink("Page.Detail.") . $entity->getId()
		));

		$this->addLink("content", array(
			"text" => "/" . $entity->getUri(),
			"link" => CMSUtil::getSiteUrl() . $entity->getUri()
		));

		$this->addLabel("udate", array(
			"text"=>CMSUtil::getRecentDateTimeText($entity->getUdate()),
			"title" => (is_numeric($entity->getUdate())) ? date("Y-m-d H:i:s", $entity->getUdate()) : null
		));
	}
}
