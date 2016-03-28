<?php

class DetailAction  extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	$logic = SOY2Logic::createInstance("logic.site.SiteConfig.SiteConfigLogic");
    	$this->setAttribute("entity",$logic->get());
    	return SOY2Action::SUCCESS;
    }
}
?>