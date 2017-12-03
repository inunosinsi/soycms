<?php

class RemovePage extends WebPage{

    function __construct($args) {
    	$id = @$args[0];
    	try{

    	$mailDAO = SOY2DAOFactory::create("MailDAO");
    	$mailDAO->delete($id);

    	}catch(Exception $e){
    		//do nothing
    	}

    	CMSApplication::jump("Mail.DraftBox");
    }
}
?>