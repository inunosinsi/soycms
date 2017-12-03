<?php

class JobConfigPage extends WebPage{

    function __construct() {
    	
    	return null;
    	
    	//parent::__construct();
    	
    	$errorMailDAO = SOY2DAOFactory::create("ErrorMailDAO");
    	
    	var_dump($errorMailDAO->get());
    	exit;
    }
}
?>