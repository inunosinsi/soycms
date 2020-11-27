<?php

class LabelListComponent extends HTMLList{

	function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$this->addLabel("label_name", array(
			"text"  =>  $entity->getBranchName(),
// 			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
// 					  ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";",
			"title" => $entity->getBranchName(),
		));

		$this->addLabel("label_entries_count",array(
			"text" => ( (int)$entity->getEntryCount())
		));

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl(),
			"title" => $entity->getBranchName(),
			"alt" => "",
		));

		$this->addLabel("label_description", array(
			"html" => nl2br(htmlspecialchars(self::trimDescription($entity->getDescription()),ENT_QUOTES,"UTF-8")),
			"title" => $entity->getDescription()
		));

		$this->addLink("detail_link_01", array(
			"title" => $entity->getCaption()." (".$entity->getEntryCount().")",
			"link"  => SOY2PageController::createLink("Entry.List")."/".$id
		));

		$this->addLink("create_link",array(
			"link" => SOY2PageController::createLink("Entry.Create") . "/" . $id
		));
	}

	private function trimDescription($str){
		return mb_strimwidth($str,0,96);
	}
}
