<?php
class EndStage extends  StageBase{

    function EndStage() {
    	
    }
    
    function execute(){
    	$redirect = @$this->wizardObj->end_redirect_address;
    	if(is_null($redirect)){
    		$redirect = "";
    	}
    	
    	$sessionStage = SOY2ActionSession::getUserSession()->setAttribute("WizardCurrentStage",null);
    	$wizObj = SOY2ActionSession::getUserSession()->setAttribute("WizardObject",null);
    	
    	$this->jump($redirect);
    }
    
    
}
?>