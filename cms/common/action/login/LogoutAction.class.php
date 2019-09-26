<?php

class LogoutAction extends SOY2Action{

    function execute() {
		//autoログインも削除
		if(isset($_COOKIE["soycms_auto_login"])){
			$dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
			try{
				$login = $dao->getByToken($_COOKIE["soycms_auto_login"]);
				setcookie("soycms_auto_login", $login->getToken(), time() - 1);
				$dao->deleteByUserId($login->getUserId());
			}catch(Exception $e){
				//
			}
		}

		UserInfoUtil::logout();

    	return SOY2Action::SUCCESS;
    }
}
