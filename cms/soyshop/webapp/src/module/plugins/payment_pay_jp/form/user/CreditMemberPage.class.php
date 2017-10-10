<?php

class CreditMemberPage extends WebPage{

	private $user;

	function __construct(){}

	function execute(){
		parent::__construct();

		$token = self::getTokenByUserId($this->user->getId());

		DisplayPlugin::toggle("register_token", isset($token));
		DisplayPlugin::toggle("no_register_token", !isset($token));

		$this->addLabel("token", array(
			"text" => $token
		));
	}

	private function getTokenByUserId($userId){
		try{
			$attrs = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO")->getByUserId($userId);
			if(!isset($attrs["payment_pay_jp_token"])) return null;
			return $attrs["payment_pay_jp_token"]->getValue();
		}catch(Exxception $e){
			return null;
		}
	}

	function setUser($user){
		$this->user = $user;
	}
}
