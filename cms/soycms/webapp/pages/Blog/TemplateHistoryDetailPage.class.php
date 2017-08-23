<?php

class TemplateHistoryDetailPage extends CMSWebPageBase {

    function __construct($arg) {
    	$pageId = @$arg[0];
    	$historyId = @$arg[1];
    	$mode = @$arg[2];
    	if(strlen($mode) ==0) $mode = "top";
    	
    	if(count($arg) <2){
    		$this->close();
    	}
    	
    	$result = $this->run("Page.History.HistoryDetailAction",array("pageId"=>$pageId, "historyId"=>$historyId));
    	
    	if(!$result->success()){
    		$this->close();
    	}
    	
    	parent::__construct();
    	
    	$templateHistory = $result->getAttribute("TemplateHistory");
    	
		$this->createAdd("date","HTMLLabel",array(
			"text"=> date("Y-m-d H:i:s", $templateHistory->getUpdateDate())
		));

		$contents = unserialize($templateHistory->getContents());
		$contents = $contents[$mode];
		$this->createAdd("content","HTMLTextArea",array(
			"value" => $contents
		));
    
		$this->createAdd("restoreForm","HTMLForm", array(
			"action" => SOY2PageController::createLink("Blog.TemplateHistory.{$pageId}.{$mode}")
		));
		
		$this->createAdd("historyId","HTMLInput",array(
			"name"  => "historyId",
			"value" => $historyId
		));		
		
		$this->createAdd("back","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.TemplateHistory.{$pageId}.{$mode}"),
			"text" => "一覧に戻る"
		));
    }
    
    function close(){
		echo "<script type=\"text/javascript\">";
		echo "window.parent.location.reload();";
		echo "</script>";
		exit;	
    }
}

