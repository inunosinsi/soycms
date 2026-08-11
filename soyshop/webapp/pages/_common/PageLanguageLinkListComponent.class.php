<?php

class PageLanguageLinkListComponent extends HTMLList{

	protected function populateItem($entity, $pageId){
	
		$this->addLink("language_detail_link", array(
			"text" =>  $entity,
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $pageId)
		));
	}
}
