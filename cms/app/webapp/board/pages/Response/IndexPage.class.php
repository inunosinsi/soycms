<?php

class IndexPage extends WebPage{

	function doPost(){

		CMSApplication::jump("Config");
		exit;

	}

    function __construct($arg) {
    	$id = @$arg[0];
    	WebPage::__construct();

    	$logic = SOY2Logic::createInstance("logic.ResponseLogic");
    	$threadLogic = SOY2Logic::createInstance("logic.ThreadLogic");

    	$thread = $threadLogic->getById($id);

    	$offset = isset($_GET["offset"])? $_GET["offset"] : 1;
		$viewcount = isset($_GET["viewcount"]) ? $_GET["viewcount"] : 100;

    	$this->createAdd("response_loop","ResponseList",array(
    		"list"=>$logic->getByThreadId($id,$offset,$viewcount),
    		"offset"=>@$_GET["offset"],
    		"viewcount"=>@$_GET["viewcount"],
    		"threadId"=>$id
    	));

    	$this->createAdd("thread_name","HTMLLabel",array(
    		"text"=>$thread->getTitle()."の返信一覧(".$thread->getResponse()."件)"
    	));

    	$this->createAdd("pager_form","HTMLForm",array(
    		"method"=>"GET"
    	));

    	$this->createAdd("offset","HTMLInput",array(
    		"name"=>"offset",
    		"value"=>$offset
    	));

    	$this->createAdd("viewcount","HTMLInput",array(
    		"name"=>"viewcount",
    		"value"=>$viewcount
    	));

    	$this->createAdd("back_link","HTMLLink",array(
    		"link"=>SOY2PageController::createLink(APPLICATION_ID)
    	));

    	$max_res = $thread->getResponse();
		$str = array();
		for($i = 1; $i<=$max_res;$i+=100){
			$str[] = '<a href="'.SOY2PageController::createLink(APPLICATION_ID . ".Response.". $thread->getId()).'?offset='.($i).'&viewcount=100">'.$i.'-</a>';
		}
    	$this->createAdd("response_pager","HTMLLabel",array(
			"html"=>implode("&nbsp;",$str)
		));


		$this->createAdd("prev_pager","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Response.".$thread->getId()) . "?offset=".( $offset-$viewcount )."&viewcount=".$viewcount,
			"visible"=>($offset - $viewcount >= 1)
		));

		$this->createAdd("next_pager","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Response.".$thread->getId()) . "?offset=".($offset+$viewcount)."&viewcount=".$viewcount,
			"visible"=>($offset+$viewcount <= $thread->getResponse())
		));

		$this->createAdd("newly_response","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Response.".$thread->getId()) . "?offset=".($thread->getResponse() - $viewcount + 1). "&viewcount=".$viewcount
		));

    }




}

class ResponseList extends HTMLList{

	private $offset;
	private $viewcount;
	private $threadId;

	function populateItem($entity){
		$this->createAdd("response_id","HTMLLabel",array("text"=>$entity->getResponseId()));
		$this->createAdd("response_name","HTMLLink",array(
			"text"=>$entity->getName(),
			"link"=>"mailto:".$entity->getEmail()
			));
		$this->createAdd("response_submitdate","HTMLLabel",array("text"=>$entity->getSubmitdate()));
		$this->createAdd("response_hash","HTMLLabel",array("text"=>"ID:".$entity->getHash()));
		$this->createAdd("response_host","HTMLLabel",array("text"=>$entity->getHost()));
		$this->createAdd("response_body","HTMLLabel",array("text"=>$entity->getBody()));

		$this->createAdd("delete_link","HTMLLink",array("link"=>SOY2PageController::createLink(APPLICATION_ID . ".Response.Remove.".$this->threadId.".".$entity->getId())));

	}

	function getOffset() {
		return $this->offset;
	}
	function setOffset($offset) {
		$this->offset = $offset;
	}

	function getThreadId() {
		return $this->threadId;
	}
	function setThreadId($threadId) {
		$this->threadId = $threadId;
	}

	function getViewcount() {
		return $this->viewcount;
	}
	function setViewcount($viewcount) {
		$this->viewcount = $viewcount;
	}
}
?>