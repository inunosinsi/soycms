<?php

class IndexPage extends WebPage{

	function doPost(){

		CMSApplication::jump("Config");
		exit;

	}

    function __construct() {
    	WebPage::__construct();

    	$logic = SOY2Logic::createInstance("logic.ThreadLogic");
    	$list = $logic->get();
    	$this->createAdd("thread_loop","ThreadList",array(
    		"list"=>$list
    	));

    	if(count($list) == 0){
 	 		DisplayPlugin::hide("exists_thread");
    	}else{
    		DisplayPlugin::hide("no_item");
    	}

    }




}

class ThreadList extends HTMLList{
	function populateItem($entity){
		$this->createAdd("thread_id","HTMLLabel",array("text"=>$entity->getId()));
		$this->createAdd("thread_title","HTMLLink",array(
			"text"=>$entity->getTitle(),
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Response.".$entity->getId())
			));
		$this->createAdd("thread_owner","HTMLLabel",array("text"=>$entity->getOwner()));
		$this->createAdd("thread_response","HTMLLabel",array("text"=>$entity->getResponse()));
		$this->createAdd("thread_cdate","HTMLLabel",array("text"=>$entity->getCdate()));
		$this->createAdd("thread_lastsubmitdate","HTMLLabel",array("text"=>$entity->getLastsubmitdate()));
		$this->createAdd("thread_detail","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Config.".$entity->getId())
			));
		$this->createAdd("thread_delete","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID . ".Thread.Remove.".$entity->getId())
		));
	}
}
?>