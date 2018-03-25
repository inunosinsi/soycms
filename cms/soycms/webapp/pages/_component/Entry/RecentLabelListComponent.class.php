<?php

class RecentLabelListComponent extends HTMLList{

	public function populateItem($entity){

		$this->addImage("label_icon", array(
			"src"=>$entity->getIconUrl(),
			"title" => $entity->getBranchName(),
			"alt" => "",
		));
		$this->addLink("label_link", array(
			"link"  => SOY2PageController::createLink("Entry.List.".$entity->getId()),
			"title" => $entity->getCaption(),
		));
		$this->addLabel("label_name", array(
			"text" => $entity->getCaption(),
			"title" => $entity->getBranchName(),
		));
		$this->addLabel("label_entries_count",array(
			"text" => ( (int)$entity->getEntryCount())
		));
	}
}
