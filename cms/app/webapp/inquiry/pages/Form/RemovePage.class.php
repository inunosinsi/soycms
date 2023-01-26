<?php

class RemovePage extends WebPage{

    function __construct($args) {
		if(soy2_check_token()){
			$id = (isset($args[0])) ? (int)$args[0] : null;

			try{
				SOY2DAOFactory::create("SOYInquiry_FormDAO")->delete($id);
		    	SOY2DAOFactory::create("SOYInquiry_ColumnDAO")->deleteByFormId($id);
			}catch(Exception $e){
				//
			}

	    	CMSApplication::jump("Form");
		}
    }
}
