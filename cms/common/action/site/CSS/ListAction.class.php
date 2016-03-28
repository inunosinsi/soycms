<?php

class ListAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.CSS.CSSLogic");
		
		$this->setAttribute("list",$logic->get());
		return SOY2Action::SUCCESS;		
    }
}
?>