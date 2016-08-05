<?php

class DeleteCategoryPage extends CMSWebPageBase{

    function __construct() {
    	if(soy2_check_token()){
			$this->run("Plugin.DeleteCategoryAction");
    	}
		$this->jump("Plugin");
    }
}
