<?php

class DetailPage extends CMSWebPageBase {

    function __construct($arg) {
    	$entryId = @$arg[0];
    	$historyId = @$arg[1];

    	if(count($arg) <2){
    		$this->jump("Entry");
    	}

    	$result = $this->run("Entry.History.HistoryDetailAction",array("entryId"=>$entryId, "historyId"=>$historyId));

    	if(!$result->success()){
    		error_log(__LINE__);
    		$this->jump("Entry.Detail.{$entryId}");
    	}

    	parent::__construct();

    	$history = $result->getAttribute("EntryHistory");

		$this->createAdd("date","HTMLLabel",array(
			"text"=> date("Y-m-d H:i:s", $history->getCdate())
		));

		$this->addModel("show_author",array(
			"visible" => UserInfoUtil::isDefaultUser(),//一応初期管理者に限定しておく
		));

		$this->addLabel("author",array(
			"text" => $history->getAuthor(). " (".$history->getUserId().")",
		));

		$this->createAdd("title","HTMLLabel",array(
			"text" => $history->getTitle()
		));
		$this->createAdd("content","HTMLLabel",array(
			"text" => $history->getContent()
		));
		$this->createAdd("more","HTMLLabel",array(
			"text" => $history->getMore()
		));

		$this->createAdd("rollback_form","HTMLForm", array(
			"action" => SOY2PageController::createLink("Entry.History.".$entryId)
		));

		$this->createAdd("id","HTMLInput",array(
			"name"  => "id",
			"value" => $historyId
		));

		$this->createAdd("back","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.History.".$entryId),
			"text" => "一覧に戻る"
		));
    }
}

