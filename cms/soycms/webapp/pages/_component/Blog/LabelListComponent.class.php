<?php

class LabelListComponent extends HTMLList{

	private $pageId;
	private $entryLabelIds = array();

	public function setPageId($pageId){
		$this->pageId = $pageId;
	}

	public function setEntryLabelIds($list){
		if(is_array($list)){
			$this->entryLabelIds = $list;
		}
	}

	protected function populateItem($label){
		$this->addLink("entry_list_link", array(
			"link" => SOY2PageController::createLink("Blog.EntryList.".$this->pageId.".".$label->getId()),
			"text" => $label->getCaption(),
			"visible" => in_array($label->getId(), $this->entryLabelIds),
			"style"=> "color:#" . sprintf("%06X",$label->getColor()).";"
			."background-color:#" . sprintf("%06X",$label->getBackgroundColor()).";",
		));
	}
}
