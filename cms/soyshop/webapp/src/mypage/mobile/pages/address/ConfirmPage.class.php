<?php
SOY2HTMLFactory::importWebPage("address.EditPage");
class ConfirmPage extends EditPage{

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$mypage = MyPageLogic::getMyPage();
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
		}

		if(isset($_POST["back"]) || isset($_POST["back_x"])){
			$this->jump("address/edit/" . $this->address_key);
		}
	}

	function ConfirmPage($args){

		if(isset($args[0])){
			$this->address_key = $args[0];
		}else{
			$this->jump("address");
		}

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす

		WebPage::WebPage();

		$address = $mypage->getAttribute("address");

		//送付先フォーム
		$this->buildSendForm($address);

		$url = soyshop_get_mypage_url() . "/address/confirm/" . $this->address_key;
		if(isset($_GET[session_name()])){
			$url = $url."?".session_name() . "=" . session_id();
		}
		$this->addForm("form", array(
			"method" => "post",
			"action" => $url
		));
	}

}
?>