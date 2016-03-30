<?php

class RemovePage extends MainMyPagePageBase{

    function RemovePage() {
    	
    	if(soy2_check_token()){
    		$mypage = MyPageLogic::getMyPage();
	
	    	$logic = SOY2Logic::createInstance("logic.user.UserLogic");
	    	if($logic->remove($mypage->getUserId())){
	    		$mypage->logout();
		
				$this->jump("withdraw/complete");
				exit;
	    	}	
    	}
    	
    	$this->jump("withdraw/?error");
    	exit;
    }
}
?>