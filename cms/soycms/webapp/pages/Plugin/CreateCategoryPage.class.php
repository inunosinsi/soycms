<?php

class CreateCategoryPage extends CMSWebPageBase{

	function doPost(){
    	if(soy2_check_token()){
			$this->run("Plugin.CreateCategoryAction");
			$this->jump("Plugin");
    	}
	}

    function CreateCategoryPage() {
    	WebPage::WebPage();
    	$this->jump("Plugin");
    }
}
?>