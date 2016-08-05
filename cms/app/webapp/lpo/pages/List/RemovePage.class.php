<?php

class RemovePage extends WebPage{

    function __construct($args) {
    	
    	$id = $args[0];

		//ディフォルト以外の時は削除
		if($id!=1){
			$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");
	    	
	    	try{
	    		$dao->deleteById($id);
	    	}catch(Exception $e){
	    		
	    	}
		}
		
		CMSApplication::jump("List");
    }
}
?>