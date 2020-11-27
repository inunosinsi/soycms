<?php

class SubLabelListComponent extends HTMLList{

	private $labelList;
	private $currentLink;
	private $pageId;

	public function setCurrentLink($link){
		$this->currentLink = $link;
	}

	public function setLabelList($list){
		$this->labelList = $list;
	}

	public function setPageId($pageId){
		$this->pageId = $pageId;
	}

	protected function populateItem($labelId){
		if(!is_numeric($labelId)) $labelId = 0;
		$label = (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : new Label();
		if(!$label instanceof Label)$label = new Label();

		$this->addImage("label_icon", array(
				"src"=>$label->getIconUrl(),
				"title" => $label->getBranchName(),
				"alt" => "",
		));
		$this->addLink("label_link", array(
				"link" => $this->currentLink ."/".$label->getId(),
				"title" => $label->getCaption(),
		));
		$this->addLabel("label_name", array(
				"text" => $label->getCaption(),
				"title" => $label->getBranchName(),
		));
		$this->addLabel("label_entries_count",array(
				"text" => ( (int)$label->getEntryCount())
		));
	}
}
