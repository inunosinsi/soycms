<?php

class TestAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	$this->setAttribute("message","Hello World");
    	
    	return SOY2Action::SUCCESS;
    }
}
?>