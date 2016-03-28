<?php

class LogoutAction extends SOY2Action{

    function execute() {

    	if(defined("SOYCMS_ASP_MODE") OR UserInfoUtil::hasOnlyOneRole()){
    		return $this->logoutFull();
    	}else{
    		return $this->logoutSite();
    	}

    }
    
    function logoutFull(){
		$this->getUserSession()->setAuthenticated(false);
		$this->getUserSession()->clearAttributes();
    	
    	return SOY2Action::SUCCESS;
    }
    
    function logoutSite(){
		$this->getUserSession()->setAttribute("Site",null);
    	
    	return SOY2Action::SUCCESS;
    }
    
}
?>