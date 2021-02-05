<?php

class UserLogic extends SOY2LogicBase{

	private $siteId;
	private $userId;

	function __construct(){}

	function getAuthorInfo(){
		$user = $this->getUser();
		return array(
			"author" => $user->getName(),
			"mailAddress" => $user->getMailAddress(),
			"url" => $user->getUrl()
		);
	}

	function getUser(){
		$old = SOYShopUtil::switchShopMode($this->siteId);

		SOY2::import("domain.config.SOYShop_DataSets");
		include_once(SOY2::RootDir() . "base/func/common.php");

		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDao->getById($this->userId);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}

		SOYShopUtil::resetShopMode($old);

		return $user;
	}

	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
	function setUserID($userId){
		$this->userId = $userId;
	}
}
