<?php

class TemplateHistoryPage extends CMSWebPageBase {

	private $pageId;
	private $mode;

	function doPost(){
		if(soy2_check_token()){
			$result = $this->run("Blog.TemplateRollbackAction",array("pageId"=>$this->pageId,"mode"=>$this->mode));
		}
		echo "<script type=\"text/javascript\">";
		echo "window.parent.location.reload();";
		echo "</script>";

		exit;
	}

	function __construct($arg) {
		$pageId = @$arg[0];
		$this->mode = @$arg[1];
		if(strlen($this->mode) ==0) $this->mode = "top";

		$this->pageId = $pageId;

		$result = $this->run("Page.History.HistoryListAction",array("pageId"=>$pageId));

		if(!$result->success()){
			$this->jump("Page");
		}

		parent::__construct();

		$list = $result->getAttribute("historyList");

		$this->createAdd("templateList","HistoryList",array(
			"list"   => $list,
			"mode"   => $this->mode,
			"pageId" => $this->pageId
		));

	}
}

class HistoryList extends HTMLList{

	private $mode;
	private $pageId;

	public function setMode($mode){
		$this->mode = $mode;
	}
	public function setPageId($id){
		$this->pageId = $id;
	}

	public function populateItem($entity){
		$contents = unserialize($entity->getContents());
		$contents = $contents[$this->mode];

		$this->createAdd("date","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.TemplateHistoryDetail.{$this->pageId}.{$entity->getId()}.{$this->mode}"),
			"text"=> date("Y-m-d H:i:s", $entity->getUpdateDate())
		));

		$this->createAdd("contents","HTMLLabel",array(
			"text"  => mb_strimwidth(strip_tags($contents),0,128,'...'),
			"title" => mb_strimwidth(strip_tags($contents),0,1000,'...'),
		));

		$this->createAdd("restoreForm","HTMLForm");

		$this->createAdd("historyId","HTMLInput",array(
			"name"=>"historyId",
			"value"=>$entity->getId()
		));
	}
}
