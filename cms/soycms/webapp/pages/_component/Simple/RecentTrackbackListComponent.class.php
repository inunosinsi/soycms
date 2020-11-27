<?php

class RecentTrackbackListComponent extends HTMLList{

	protected function populateItem($entity){

		$blog = (isset($entity->info["blog"])) ? $entity->info["blog"] : new BlogPage();
		$entry = (isset($entity->info["entry"])) ? $entity->info["entry"] : new Entry();

		$title = ((strlen($entity->getTitle())==0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle());
		$title .= strlen($entity->getBlogName()) == 0  ? "" : " (".$entity->getBlogName().")";

		$this->addLink("title", array(
			"link"=>SOY2PageController::createLink("Blog.Trackback.".$blog->getId()),
			"text"=>$title
		));
		$this->addLabel("content", array(
			"text"=>$entry->getTitle() . " (" . $blog->getTitle() . ")"
		));
		$this->addLabel("udate", array(
			"text" => (is_numeric($entity->getSubmitDate())) ? CMSUtil::getRecentDateTimeText($entity->getSubmitDate()) : "",
			"title" => (is_numeric($entity->getSubmitDate())) ? date("Y-m-d H:i:s", $entity->getSubmitDate()) : ""
		));
	}
}
