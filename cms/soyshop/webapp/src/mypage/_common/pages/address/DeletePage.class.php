<?php
class DeletePage extends MainMyPagePageBase{

	function DeletePage($args){

		$mypage = MyPageLogic::getMyPage();
		
		//ログインしていなかったら飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

		if(isset($args[0])){
			$address_key = $args[0];
		}else{
			$this->jump("address");
		}

		if(soy2_check_token() && isset($address_key)){
			$user = $this->getUser();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			//削除
			$list = $user->getAddressListArray();
			unset($list[$address_key]);
			$user->setAddressList($list);

			try{
				$userDAO->update($user);
				$this->jump("address");
			}catch(Exception $e){

			}
		}

		$this->jump("address");
	}
}
?>