<?php

class DeleteCategoryPage extends CMSWebPageBase{

    function DeleteCategoryPage() {
    	if(soy2_check_token()){
			$this->run("Plugin.DeleteCategoryAction");
    	}
		$this->jump("Plugin");
    }
}
