<?php

class RecentLabelListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addImage("label_icon", array(
			"src"=>$entity->getIconUrl(),
		));
		$this->addLink("label_link", array(
			"link"=>SOY2PageController::createLink("Entry.List.".$entity->getId())
		));
		$this->addLabel("label_title", array(
			"text" => $entity->getDisplayCaption(),
		));

		$this->addLabel("label_entries_count", array(
			"text" => $entity->getEntryCount()
		));
	}
}
