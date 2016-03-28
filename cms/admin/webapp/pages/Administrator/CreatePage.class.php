<?php

class CreatePage extends CMSUpdatePageBase{
	
	var $failed = false;

	function doPost(){
		
		if(soy2_check_token()){
			$res = $this->createAdministrator();
			
			if($res !== false){
	    		$this->addMessage("CREATE_SUCCESS");
				SOY2PageController::jump("Administrator.SiteRole." . $res);
			}
		}
		
		$this->failed = true;
	}

    function CreatePage() {
    	if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("Administrator");
    	}
    	WebPage::WebPage();
    	$this->addForm("change_password_form");
    	
    	$this->addModel("error", array(
    		"visible" => $this->failed
    	));
    }
    
    /**
     * 管理者を追加する。
     * Administrator.CreateActionを呼び出す
     */
    function createAdministrator(){
    	$action = SOY2ActionFactory::createInstance("Administrator.CreateAction");
    	$result = $action->run();
    	
    	if($result->success()){
    		return $result->getAttribute("id");	
    	}else{
    		return false;
    	}
    }
}
?>