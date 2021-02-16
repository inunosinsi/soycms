<?php

class LogoutAction extends SOY2Action{

    function execute() {
		//autoログインも削除
		if(isset($_COOKIE["soycms_auto_login"])){
			$dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
			try{
				$login = $dao->getByToken($_COOKIE["soycms_auto_login"]);
				soy2_setcookie("soycms_auto_login");
				$dao->deleteByUserId($login->getUserId());
			}catch(Exception $e){
				//
			}
		}

		UserInfoUtil::logout();

    	return SOY2Action::SUCCESS;
    }
}
