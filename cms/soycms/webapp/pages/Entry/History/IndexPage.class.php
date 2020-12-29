<?php

class IndexPage extends CMSWebPageBase {

	private $entryId;
	private $page = 0;

	function doPost(){
		if(soy2_check_token()){
			$result = $this->run("Entry.History.RollbackAction",array("entryId"=>$this->entryId));
			if($result->success()){

				echo "<script type=\"text/javascript\">";
				echo "window.parent.location.reload();";
				echo "</script>";

				exit;
			}
		}
	}

	function __construct($arg) {
		if(isset($arg[0]))$this->entryId = $arg[0];
		if(isset($arg[1]))$this->page    = $arg[1];

		parent::__construct();

		$result = $this->run("Entry.History.HistoryListAction",array(
			"entryId" => $this->entryId,
			"page"  => $this->page,
		));

		if(!$result->success()){
			$this->jump("Entry.Detail.".$this->entryId);
		}

		$list  = $result->getAttribute("historyList");
		$hasNext = $result->getAttribute("hasNext");
		$hasPrev = $result->getAttribute("hasPrev");

		$this->createAdd("history_list","HistoryList",array(
			"list"   => $list,
			"entryId" => $this->entryId
		));

		$this->addLink("prev_link",array(
			"link" => SOY2PageController::createLink("Entry.History.".$this->entryId.($this->page>1 ? ".".($this->page-1) : "")),
			"visible" => $hasPrev
		));

		$this->addLink("next_link",array(
			"link" => SOY2PageController::createLink("Entry.History.".$this->entryId.".".($this->page+1)),
			"visible" => $hasNext
		));

	}
}

class HistoryList extends HTMLList{

	private $entryId;

	function populateItem($entity,$key,$counter,$length){
		$this->createAdd("date","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.History.Detail.{$this->entryId}.{$entity->getId()}"),
			"text"=> (is_numeric($entity->getCdate())) ? date("Y-m-d H:i:s", $entity->getCdate()) : ""
		));

		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId(),
		));

		$this->createAdd("action","HTMLLabel",array(
			"text" => $entity->getActionTypeText(),
		));

		$this->createAdd("published","HTMLLabel",array(
			"text" => $entity->getPublishStatusText(),
		));

		$this->createAdd("change","HTMLLabel",array(
			"text" => $entity->getChangeText(),
		));

		$this->createAdd("rollback_form","HTMLForm",array(
			"disabled" => ($counter == 1)
		));

		$this->createAdd("rollback_id","HTMLInput",array(
			"name" => "historyId",
			"value" => $entity->getId(),
		));

		$this->createAdd("rollback_button","HTMLModel",array(
			"visible" => ($counter > 1)
		));
	}

	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
}
