<?php

class WithdrawPage extends CMSWebPageBase{

	var $failed;
	
	function setFailed($flag){
		$this->failed = $flag;
	}
	
	function doPost(){
    	if(soy2_check_token()){

			$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
	    	$user = $dao->getById(UserInfoUtil::getUserId());
	    	if($user->getPassword() == crypt($_POST["password"],$user->getEmail())){
				$this->jump("Login.UserInfo.WithdrawConfirm",array(
					"check"=>true
				));	
	    	}else{
	    		$this->jump("Login.UserInfo.Withdraw",array(
					"failed" => true
				));
	    	}

    	}
	}
	

    function __construct(){
    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
    	$user = $dao->getById(UserInfoUtil::getUserId());
    	
    	//デモサイトは退会不可にする
    	if(!$user->getIsEnableWithdraw()){
    		$this->jump("Login.UserInfo");
    	}

    	parent::__construct();
    	
    	$this->createAdd("withdraw_form","HTMLForm");
    	
    	if($this->failed){
			$this->createAdd("caution","HTMLLabel",array(
				"text"=>CMSMessageManager::get("SOYCMS_FAILURE_TO_WITHDRAW")
			));
		}else{
			$this->createAdd("caution","HTMLLabel",array(
				"text"=>"",
				"visible"=>false
			));
		}
		
		
		
			
    }
}
?>