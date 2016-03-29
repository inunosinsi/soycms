<?php

class JobConfigPage extends WebPage{

    function JobConfigPage() {
    	
    	return null;
    	
    	//WebPage::WebPage();
    	
    	$errorMailDAO = SOY2DAOFactory::create("ErrorMailDAO");
    	
    	var_dump($errorMailDAO->get());
    	exit;
    }
}
?>