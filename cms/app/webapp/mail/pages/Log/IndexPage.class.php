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

		$logs = self::getLogs();
		krsort($logs);
		$logCnt = count($logs);

		DisplayPlugin::toggle("message", $logCnt === 0);
		DisplayPlugin::toggle("is_log", $logCnt > 0);

    	$this->createAdd("log_list", "_common.LogListComponent",array(
    		"list" => $logs
    	));

    	$this->addForm("log_form", array(
    		"visible" => ($logCnt > 0)
    	));
    }

    private function getLogs(){
    	return SOYMailLog::get();
    }
}
