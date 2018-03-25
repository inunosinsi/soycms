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

		$visible = array_key_exists($labelId, $this->labelList);

		if($visible){
			$label = $this->labelList[$labelId];
		}

		if(!$visible OR !$label instanceof Label){
			$label = new Label();
		}

		$this->createAdd("label_icon","HTMLImage",array(
			"src"=>$label->getIconUrl(),
			"title" => $label->getBranchName(),
			"alt" => "",
		));
		$this->createAdd("label_link","HTMLLink",array(
			"link" => $this->currentLink ."/".$label->getId(),
			"title" => $label->getCaption(),
		));
		$this->createAdd("label_name","HTMLLabel",array(
			"text" => $label->getCaption(),
			"title" => $label->getBranchName(),
		));
		$this->addLabel("label_entries_count",array(
			"text" => ( (int)$label->getEntryCount())
		));
	}
}
