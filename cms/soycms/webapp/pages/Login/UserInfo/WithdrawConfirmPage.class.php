<?php

class WithdrawConfirmPage extends CMSWebPageBase{

	var $check;
	
	function setCheck($check){
		$this->check = $check;
	}

	function doPost(){
		if(isset($_POST["withdraw_apply"])){
		
	    	if(soy2_check_token()){
				SOY2::import("action.register.WithdrawAction");
				$result = $this->run("WithdrawAction",array("siteId"=>UserInfoUtil::getSite()->getId()));
				if($result->success()){
					SOY2::import("action.login.LogoutAction");
					$this->run("LogoutAction");
					$this->jump("");
				}else{
					$this->jump("Login.UserInfo.Withdraw",array(
						"failed" => true
					));
				}
	    	}else{
				$this->jump("Login.UserInfo.Withdraw");
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
    	if(!$this->check){
    		$this->jump("Login.UserInfo.Withdraw");
    		exit;
    	}
    	$this->createAdd("withdraw_apply_form","HTMLForm");
    	$this->createAdd("withdraw_cancel_form","HTMLForm");
    }
}
?>