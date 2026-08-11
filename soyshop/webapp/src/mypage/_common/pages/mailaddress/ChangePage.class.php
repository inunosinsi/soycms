<?php

class ChangePage extends MainMyPagePageBase{

    function __construct() {
		if(!isset($_GET["q"]) || !strlen($_GET["q"])) $this->jumpToTop();
		$res = self::_executeChange(trim($_GET["q"]));

    	parent::__construct();

		DisplayPlugin::toggle("success", $res);
		DisplayPlugin::toggle("failed", !$res);

		$this->addLink("top_link", array(
            "link" => soyshop_get_mypage_top_url()
        ));
    }

	private function _executeChange($token){
		$mailTokenDao = SOY2DAOFactory::create("user.SOYShop_MailAddressTokenDAO");

		//time_limitが今より古い場合はすべて削除
		$mailTokenDao->deleteOldObjects();

		try{
			$tokenObj = $mailTokenDao->getByToken($token);
		}catch(Exception $e){
			return false;
		}

		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDao->getById($tokenObj->getUserId());
			$user->setMailAddress($tokenObj->getNew());
			$userDao->update($user);
			$mailTokenDao->deleteByUserId($user->getId());
		}catch(Exception $e){
			return false;
		}

		return true;
	}
}
