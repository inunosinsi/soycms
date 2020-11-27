<?php
//LabelをCategoryのように振る舞う
class CategoryListComponent extends HTMLList{

	public static $tabIndex = 0;
	private $pageId;

	function populateItem($entity){

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));

		$this->createAdd("label_name","HTMLLabel",array(
			"text"=> $entity->getBranchName(),
			"style"=> "cursor:pointer;color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";margin:5px",
			"onclick"=>'postReName('.$entity->getId().',"'.addslashes($entity->getDescription()).'")'
		));

		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink("Blog.Remove." .$this->pageId . "." .$entity->getId()),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),
		));

		$this->createAdd("description","HTMLLabel",array(
			"text"=> (trim($entity->getDescription())) ? $entity->getDescription() : CMSMessageManager::get("SOYCMS_CLICK_AND_EDIT"),
			"onclick"=>'postDescription('.$entity->getId().',"'.addslashes($entity->getCaption()).'","'.addslashes($entity->getDescription()).'")'
		));

		//記事数
//		$this->createAdd("entry_count","HTMLLabel",array(
//			"text"=> $entity->getEntryCount(),
//		));
	}

	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}
