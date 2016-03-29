<?php

class RemovePage extends WebPage{

    function RemovePage($args) {
    	$dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	
    	$id = @$args[0];
    	
    	$dao->delete($id);
    	
    	$columnDAO = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    	$columnDAO->deleteByFormId($id);
    	    	
    	CMSApplication::jump("Form");
    }
}
?>