<?php

class LabelListComponent extends HTMLList{
	public static $tabIndex = 0;

	function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$id.");"
		));

		$this->addLabel("label_name", array(
			"text"=> $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";;font-size:initial;"
		));

		$this->addInput("display_order", array(
			"name"	 => "display_order[".$id."]",
			"value"	=> $entity->getDisplayOrder(),
			"tabindex" => self::$tabIndex++
		));

		$this->addLink("label_link", array(
			"link"=>SOY2PageController::createLink("Entry.List.".$id)
		));

		$this->addLink("detail_link", array(
			"link"=>SOY2PageController::createLink("Label.Detail.".$id)
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Label.Remove.".$id),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),
		));

		$this->addLabel("description", array(
			"text"=> (trim($entity->getDescription())) ? $entity->getDescription() : CMSMessageManager::get("SOYCMS_CLICK_AND_EDIT"),
			"onclick"=>'postDescription('.$id.',"'.addslashes($entity->getCaption()).'","'.addslashes($entity->getDescription()).'")'
		));

		//記事数
//		$this->addLabel("entry_count", array(
//			"text"=> $entity->getEntryCount(),
//		));

		//記事のエクスポートで利用
		$this->addCheckBox("label_checkbox", array(
			"name" => "Label[]",
			"value" => $id,
			"label" => $entity->getCaption()
		));
	}
}
