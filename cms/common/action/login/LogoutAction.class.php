<?php

class LogoutAction extends SOY2Action{

    function execute() {
    	UserInfoUtil::logout();

		//autoログインも削除
		if(isset($_COOKIE["soycms_auto_login"])){
			$dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
			try{
				$login = $dao->getByToken($_COOKIE["soycms_auto_login"]);
				$login->setLimit(time() - 1);
				setcookie("soycms_auto_login", $login->getToken(), time() - 1);
				$dao->update($login);
			}catch(Exception $e){
				//
			}
		}

    	return SOY2Action::SUCCESS;
    }
}
