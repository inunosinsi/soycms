<?php

class LogoutAction extends SOY2Action{

    function execute() {
    	UserInfoUtil::logout();
    	return SOY2Action::SUCCESS;
    }
}
?>