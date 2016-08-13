<?php

class RemovePage extends WebPage{

    function __construct($arg) {
    	$threadId = $arg[0];
    	$responseId = $arg[1];

    	$offset = isset($_GET["offset"])? $_GET["offset"] : null;
		$viewcount = isset($_GET["viewcount"]) ? $_GET["viewcount"] : null;


    	$logic = SOY2Logic::createInstance("logic.ResponseLogic");

    	$logic->delete($threadId,$responseId);

		if(is_null($offset)){
			CMSApplication::jump("Response.".$threadId);
		}else{
			CMSApplication::jump("Response.".$threadId."?offset=".$offset."&viewcount=".$viewcount);
		}
    	exit;


    }
}
?>