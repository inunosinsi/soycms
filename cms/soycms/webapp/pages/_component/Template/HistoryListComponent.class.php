<?php

class HistoryListComponent extends HTMLList{

	private $mode;
	private $pageId;

	public function setMode($mode){
		$this->mode = $mode;
	}
	public function setPageId($id){
		$this->pageId = $id;
	}

	public function populateItem($entity){
		$contents = (preg_match('/a:[\d]/', (string)$entity->getContents())) ? unserialize($entity->getContents()) : array();
		$contents = (isset($contents[$this->mode])) ? (string)$contents[$this->mode] : "";
		
		$this->addLink("date", array(
			"link" => SOY2PageController::createLink("Blog.TemplateHistoryDetail.{$this->pageId}.{$entity->getId()}.{$this->mode}"),
			"text"=> date("Y-m-d H:i:s", (int)$entity->getUpdateDate())
		));

		$this->addLabel("contents", array(
			"text"  => mb_strimwidth(strip_tags($contents),0,128,'...'),
			"title" => mb_strimwidth(strip_tags($contents),0,1000,'...'),
		));

		$this->addForm("restoreForm");

		$this->addInput("historyId", array(
			"name"=>"historyId",
			"value"=>$entity->getId()
		));
	}
}
