<?php

//TopPage.class.phpへリダイレクト用
class IndexPage extends MobileMyPagePageBase{

    function IndexPage(){
    	WebPage::WebPage();
    	
    	$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()){
			$this->jumpToTop();
		}else{
			$this->jump("login");
		}
    	
    }
}
?>