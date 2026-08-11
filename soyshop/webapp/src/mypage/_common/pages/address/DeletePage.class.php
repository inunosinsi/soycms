<?php
class DeletePage extends MainMyPagePageBase{

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック
		if(!isset($args[0]) || !soy2_check_token()) $this->jump("address");

		$address_key = $args[0];
		$user = $this->getUser();
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//削除
		$list = $user->getAddressListArray();
		unset($list[$address_key]);
		$user->setAddressList($list);

		try{
			$userDAO->update($user);
		}catch(Exception $e){
			//
		}

		$this->jump("address");
	}
}
