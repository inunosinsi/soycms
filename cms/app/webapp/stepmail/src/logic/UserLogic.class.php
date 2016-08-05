<?php

class UserLogic extends SOY2LogicBase{
	
	private $userDao;
	
	function __construct(){
		$this->userDao = SOY2DAOFactory::create("SOYShop_UserDAO");
	}
	
	function getMailAddress($userId){		
		try{
			return $this->getUser($userId)->getMailAddress();
		}catch(Exception $e){
			return null;
		}
	}
	
	function getUserIdByMailAddress($mailaddress){
		try{
			return (int)$this->userDao->getByMailAddress(trim($mailaddress))->getId();
		}catch(Exception $e){
			return null;
		}
	}
	
	function getUser($userId){
		try{
			return $this->userDao->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	/**
	 * 入力したアドレスのユーザが名簿に登録されていない場合は、名簿に登録する
	 * @param string mailaddress
	 * @return integer id
	 */
	function register($mailaddress){
		$user = new SOYShop_User();
		$user->setMailAddress(trim($mailaddress));
		$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
		$user->setRealRegisterDate(time());
		try{
			return $this->userDao->insert($user);	//返値がid
		}catch(Exception $e){
			return null;
		}
	}
}
?>