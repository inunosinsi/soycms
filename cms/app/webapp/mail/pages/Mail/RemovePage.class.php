<?php

class RemovePage extends WebPage{

    function __construct($args) {
		if(soy2_check_token()){
			$id = (isset($args[0])) ? (int)$args[0] : null;
	    	try{
				SOY2DAOFactory::create("MailDAO")->delete($id);
	    	}catch(Exception $e){
	    		//do nothing
	    	}

	    	CMSApplication::jump("Mail.DraftBox");
		}
    }
}
