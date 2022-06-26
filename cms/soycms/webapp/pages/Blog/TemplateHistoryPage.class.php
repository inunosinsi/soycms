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

		$this->createAdd("templateList", "_component.Template.HistoryListComponent", array(
			"list"   => $list,
			"mode"   => $this->mode,
			"pageId" => $this->pageId
		));
	}
}
