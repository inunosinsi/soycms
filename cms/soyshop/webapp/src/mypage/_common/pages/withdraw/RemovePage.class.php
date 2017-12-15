<?php

class RemovePage extends MainMyPagePageBase{

    function __construct() {

    	if(soy2_check_token()){
    		$mypage = MyPageLogic::getMyPage();

	    	if(SOY2Logic::createInstance("logic.user.UserLogic")->remove($mypage->getUserId())){
	    		$mypage->logout();

				$this->jump("withdraw/complete");
				exit;
	    	}
    	}

    	$this->jump("withdraw/?error");
    	exit;
    }
}
