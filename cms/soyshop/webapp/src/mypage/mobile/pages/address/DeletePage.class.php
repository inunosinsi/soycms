<?php
class DeletePage extends MobileMyPagePageBase{

	function DeletePage($args){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす

		if(isset($args[0])){
			$address_key = $args[0];
		}else{
			$this->jump("address");
		}

		if(soy2_check_token()){
			$user = $this->getUser();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

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