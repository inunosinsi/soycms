<?php

class TemplateHistoryPage extends CMSWebPageBase {

	private $pageId;

	public function setPageId($pageId){
		$this->pageId = $pageId;

	}

	function doPost(){
		if(soy2_check_token()){
			$result = $this->run("Page.History.TemplateRollbackAction",array("pageId"=>$this->pageId));
			if($result->success()){

				echo "<script type=\"text/javascript\">";
				echo "window.parent.location.reload();";
				echo "</script>";

				exit;
			}
		}
	}

	function __construct($arg) {
		$pageId = @$arg[0];
		$this->pageId = $pageId;

		$result = $this->run("Page.History.HistoryListAction",array("pageId"=>$this->pageId));

		if(!$result->success()){
			$this->jump("Page");
		}

		parent::__construct();

		$list = $result->getAttribute("historyList");

		$this->createAdd("templateList","HistoryList",array(
			"list"   => $list,
			"pageId" => $this->pageId
		));

	}
}

class HistoryList extends HTMLList{

	private $pageId;

	public function populateItem($entity){
		$this->createAdd("date","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.TemplateHistoryDetail.{$this->pageId}.{$entity->getId()}"),
			"text"=> date("Y-m-d H:i:s", $entity->getUpdateDate())
		));

		$this->createAdd("contents","HTMLLabel",array(
			"text" => mb_strimwidth(strip_tags($entity->getContents()),0,128,'...'),
			"title" => mb_strimwidth(strip_tags($entity->getContents()),0,1000,'...')
		));

		$this->createAdd("restoreForm","HTMLForm");

		$this->createAdd("historyId","HTMLInput",array(
			"name"=>"historyId",
			"value"=>$entity->getId()
		));
	}

	public function setPageId($id){
		$this->pageId = $id;
	}
}
