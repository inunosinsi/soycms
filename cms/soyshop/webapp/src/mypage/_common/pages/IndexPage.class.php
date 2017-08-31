<?php

//TopPage.class.phpへリダイレクト用
class IndexPage extends MainMyPagePageBase{

    function __construct(){
    	parent::__construct();

    	$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()){
			$this->jumpToTop();
		}else{
			$this->jump("login");
		}

    }
}
