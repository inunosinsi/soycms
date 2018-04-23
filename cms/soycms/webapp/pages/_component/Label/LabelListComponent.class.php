<?php

class LabelListComponent extends HTMLList{
	public static $tabIndex = 0;

	function populateItem($entity){

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));

		$this->addLabel("label_name", array(
			"text"=> $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";;font-size:initial;"
		));

		$this->addInput("display_order", array(
			"name"	 => "display_order[".$entity->getId()."]",
			"value"	=> $entity->getDisplayOrder(),
			"tabindex" => self::$tabIndex++
		));

		$this->addLink("label_link", array(
			"link"=>SOY2PageController::createLink("Entry.List.".$entity->getId())
		));

		$this->addLink("detail_link", array(
			"link"=>SOY2PageController::createLink("Label.Detail.".$entity->getId())
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Label.Remove.".$entity->getId()),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),
		));

		$this->addLabel("description", array(
			"text"=> (trim($entity->getDescription())) ? $entity->getDescription() : CMSMessageManager::get("SOYCMS_CLICK_AND_EDIT"),
			"onclick"=>'postDescription('.$entity->getId().',"'.addslashes($entity->getCaption()).'","'.addslashes($entity->getDescription()).'")'
		));

		//記事数
//		$this->addLabel("entry_count", array(
//			"text"=> $entity->getEntryCount(),
//		));

		//記事のエクスポートで利用
		$this->addCheckBox("label_checkbox", array(
			"name" => "Label[]",
			"value" => $entity->getId(),
			"label" => $entity->getCaption()
		));
	}
}
