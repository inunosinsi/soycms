<?php

class TemplateHistoryDetailPage extends CMSWebPageBase {

	function __construct($arg) {
		$pageId = @$arg[0];
		$historyId = @$arg[1];

		if(count($arg) <2){
			$this->jump("Page");
		}

		$result = $this->run("Page.History.HistoryDetailAction",array("pageId"=>$pageId, "historyId"=>$historyId));

		if(!$result->success()){
			$this->jump("Page.Detail.{$pageId}");
		}

		parent::__construct();

		$templateHistory = $result->getAttribute("TemplateHistory");

		$this->createAdd("date","HTMLLabel",array(
			"text"=> date("Y-m-d H:i:s", $templateHistory->getUpdateDate())
		));

		$this->createAdd("content","HTMLTextArea",array(
			"value" => $templateHistory->getContents()
		));

		$this->createAdd("restoreForm","HTMLForm", array(
			"action" => SOY2PageController::createLink("Page.TemplateHistory.".$pageId)
		));

		$this->createAdd("historyId","HTMLInput",array(
			"name"  => "historyId",
			"value" => $historyId
		));

		$this->createAdd("back","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.TemplateHistory.".$pageId),
			"text" => "一覧に戻る"
		));
	}
}

