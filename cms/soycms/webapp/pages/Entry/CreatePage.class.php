<?php

class CreatePage extends CMSWebPageBase{

	var $labels;

    function __construct($arg) {

    	$this->labels = $arg;

    	$this->jump("Entry.Detail",array(
    		"initLabelList" => $arg
    	));

    	exit;
    }
}
