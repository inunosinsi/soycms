<?php

class LogoutAction extends SOY2Action{

    function execute() {

    	if(defined("SOYCMS_ASP_MODE") OR UserInfoUtil::hasOnlyOneRole()){
    		return $this->logoutFull();
    	}else{
    		return $this->logoutSite();
    	}

    }

    function logoutFull(){
		$this->getUserSession()->setAuthenticated(false);
		$this->getUserSession()->clearAttributes();

		//autoログインも削除
		if(isset($_COOKIE["soycms_auto_login"])){
			$old["dsn"] = SOY2DAOConfig::Dsn();
			$old["user"] = SOY2DAOConfig::user();
			$old["pass"] = SOY2DAOConfig::pass();

			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
			try{
				$dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
				$login = $dao->getByToken($_COOKIE["soycms_auto_login"]);
				soy2_setcookie("soycms_auto_login");
				$dao->deleteByUserId($login->getUserId());
			}catch(Exception $e){
				var_dump($e);
				//
			}

			SOY2DAOConfig::Dsn($old["dsn"]);
			SOY2DAOConfig::user($old["user"]);
			SOY2DAOConfig::pass($old["pass"]);
		}

    	return SOY2Action::SUCCESS;
    }

    function logoutSite(){
		$this->getUserSession()->setAttribute("Site", null);
    	return SOY2Action::SUCCESS;
    }
}
