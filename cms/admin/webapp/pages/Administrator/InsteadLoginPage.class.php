<?php

//代わりにログイン
class InsteadLoginPage extends CMSUpdatePageBase{

	function __construct($args){
		if(!isset($args[0]) || !is_numeric($args[0])){
			$this->jump("");
			exit;
		}

		$adminId = (int)$args[0];

		try{
			$user = SOY2DAOFactory::create("admin.AdministratorDAO")->getById($adminId);
		}catch(Exception $e){
			$this->jump("");
			exit;
		}

		UserInfoUtil::logout();
		UserInfoUtil::login($user);

		$session = SOY2ActionSession::getUserSession();
		$session->setAttribute("instead_login", 1);

		$this->jump("");
		exit;
	}
}
