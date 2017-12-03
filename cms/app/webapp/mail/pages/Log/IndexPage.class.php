<?php

class IndexPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token()){
			SOYMailLog::clear(true);
		}
		
		CMSApplication::jump("Log?clear");
	}
	
    function __construct() {
    	parent::__construct();

		$logs = $this->getLogs();
		
		krsort($logs);

    	$this->createAdd("log_list","LogList",array(
    		"list" => $logs
    	));
    	
    	$this->createAdd("message","HTMLModel",array(
    		"visible" => (count($logs)<1)
    	));
    	
    	$this->createAdd("log_form","HTMLForm",array(
    		"visible" => (count($logs)>0)
    	));
    }

    function getLogs(){
    	return SOYMailLog::get();
    }
    
}

class LogList extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("log_time","HTMLLabel",array(
			"text" => (date("Y-m-d H:i:s",$entity->getTime())) . " - " . $entity->getContent(),
			"href" => "javascript:void(0);",
			"onclick" => "toggle_content('log_content_".$entity->getId()."');return 0;",
		));
		
		$this->createAdd("log_content","HTMLLabel",array(
			"html" => nl2br(htmlspecialchars($entity->getMore())),
			"attr:id" => "log_content_" . $entity->getId() 
		));
		
	}
}
?>