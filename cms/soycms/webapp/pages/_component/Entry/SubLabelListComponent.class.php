<?php

class SubLabelListComponent extends HTMLList{

	private $labelList;
	private $currentLink;

	public function setCurrentLink($link){
		$this->currentLink = $link;
	}

	public function setLabelList($list){
		$this->labelList = $list;
	}

	protected function populateItem($labelId){
		$labelId = (is_numeric($labelId)) ? (int)$labelId : 0;

		$visible = array_key_exists($labelId, $this->labelList);

		if($visible) $label = (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : new Label();

		if(!$visible || !$label instanceof Label) $label = new Label();

		$this->addImage("label_icon", array(
			"src" => $label->getIconUrl(),
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
