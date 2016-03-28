<?php

class ChangeEmailPage extends CMSWebPageBase{

    function ChangeEmailPage() {
    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
    	$user = $dao->getById(UserInfoUtil::getUserId());
    	
    	//デモサイトでは変更不可
    	if(!$user->getIsEnableWithdraw()){
    		$this->jump("Login.UserInfo");
    	}
    	
    	//TODO メールアドレス変更の実装
    	
    	WebPage::WebPage();
    }
}
?>