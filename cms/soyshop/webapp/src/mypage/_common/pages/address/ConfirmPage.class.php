<?php
SOY2HTMLFactory::importWebPage("address.EditPage");
class ConfirmPage extends EditPage{

	function doPost(){

		//保存
		if(soy2_check_token() && soy2_check_referer()){

			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$mypage = $this->getMyPage();
				$user = $this->getUser();
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

				$list = $user->getAddressListArray();
				$list[$this->address_key] = $mypage->getAttribute("address");

				$user->setAddressList($list);

				try{
					$userDAO->update($user);
					$mypage->clearAttribute("address");
					$this->jump("address");
				}catch(Exception $e){

				}
			}

			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$this->jump("address/edit/" . $this->address_key);
			}
		}
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック
		if(!isset($args[0])) $this->jump("address");

		$this->address_key = $args[0];

		parent::__construct($args);

		$address = $this->getMyPage()->getAttribute("address");

		//送付先フォーム
		$this->buildSendForm($address);
		$this->addForm("form");
	}
}
