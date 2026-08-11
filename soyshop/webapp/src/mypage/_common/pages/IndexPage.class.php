<?php

//TopPage.class.phpへリダイレクト用
class IndexPage extends MainMyPagePageBase{

    function __construct(){
    	parent::__construct();

		if($this->getMyPage()->getIsLoggedin()){
			$this->jumpToTop();
		}else{
			$this->jump("login");
		}
    }
}
