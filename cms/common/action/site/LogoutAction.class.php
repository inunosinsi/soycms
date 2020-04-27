<?php

class LogoutAction extends SOY2Action{

    function execute() {

    	if(defined("SOYCMS_ASP_MODE") OR UserInfoUtil::hasOnlyOneRole()){
    		return self::_logoutFull();
    	}else{
    		return self::_logoutSite();
    	}
    }

    private function _logoutFull(){
		$this->getUserSession()->setAuthenticated(false);
		$this->getUserSession()->clearAttributes();

		if(isset($_COOKIE["soycms_auto_login"])){
			$old = CMSUtil::switchDsn();
			$dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
			try{
				$login = $dao->getByToken($_COOKIE["soycms_auto_login"]);
				setcookie("soycms_auto_login", $login->getToken(), time() - 1);
				$dao->deleteByUserId($login->getUserId());
			}catch(Exception $e){
				//
			}
			//CMSUtil::resetDsn($old);
		}

    	return SOY2Action::SUCCESS;
    }

    private function _logoutSite(){
		$this->getUserSession()->setAttribute("Site",null);

    	return SOY2Action::SUCCESS;
    }

}
