<?php

class WizardDetectAction extends SOY2Action{

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$pages = $dao->getPagesWithoutErrorPage();
    	
    	if(count($pages)== 0){
    		$this->setAttribute("detect",true);
    	}else{
    		$this->setAttribute("detect",false);
    	}
    	
    	return SOY2Action::SUCCESS;
    
    }
}
?>