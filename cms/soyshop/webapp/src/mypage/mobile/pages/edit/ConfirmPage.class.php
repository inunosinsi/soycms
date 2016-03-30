<?php
SOY2HTMLFactory::importWebPage("edit.IndexPage");
class ConfirmPage extends IndexPage{

	function doPost(){

		//保存
		if(soy2_check_token()){

			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$mypage = MyPageLogic::getMyPage();
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

				//登録情報
				$edit = $mypage->getAttribute("edit_user_info");

				try{
					//元のデータを上書きしないように
					$user = $this->getUser();
					SOY2::cast($user,$edit);

					//IDは直接指定する（他のユーザーの情報を上書きしないように）
					$user->setId($this->getUserId());

				}catch(Exception $e){
					$this->jump("edit");
				}

				//パスワードの引き継ぎ
				if(tstrlen($user->getPassword()) > 1){
					//変更
					$user->setPassword($user->hashPassword($user->getPassword()));
				}else{
					//入力なしは変更なし。
					$user->setPassword($this->getUser()->getPassword());
				}

				try{
					$userDAO->update($user);
					$mypage->clearAttribute("edit_user_info");
					$session = false;
					if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
						if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
							$session = true;
						}
					}
					$this->jump("edit/complete",$session);
				}catch(Exception $e){

				}

			}

			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$session = false;
				if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
					if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
						$session = true;
					}
				}
				$this->jump("edit",$session);
			}
		}


		//郵便番号での住所検索

	}

	function ConfirmPage(){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//すでにログインしていたら飛ばす

		$user = $mypage->getUserInfo();
		$user->setId($this->getUserId());
		if(is_null($user))$user = $this->getUser();

		//顧客情報フォーム
		$this->buildForm($user);

		$url = soyshop_get_mypage_url() . "/edit/confirm";
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