<?php
SOY2HTMLFactory::importWebPage("edit.IndexPage");
class ConfirmPage extends IndexPage{

	function doPost(){

		//保存
		if(soy2_check_token() && soy2_check_referer()){

			$mypage = $this->getMyPage();

			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

				//セッションから取得（ただしIDが空）
				$user = $mypage->getUserInfo();

				$imagePath = "";

				//削除フラグがある場合はimagePathをnullにして更新した後、画像を削除する
				$isImageDelete = $mypage->getAttribute("user.edit.delete_image");
				if($isImageDelete === true){
					$imagePath = $user->getImagePath();
					$user->setImagePath(null);
				}

				try{
					$userDAO->update($user);
				}catch(Exception $e){
					$mypage->addErrorMessage("update", "更新に失敗しました。");
					$this->jump("edit");
				}

				//ユーザカスタムフィールドの値を保存する
				SOYShopPlugin::load("soyshop.user.customfield");
				SOYShopPlugin::invoke("soyshop.user.customfield", array(
					"mode" => "register",
					"app" => $mypage,
					"userId" => $user->getId()
				));

				//新しい画像を登録するとき、古い画像を削除する
				$oldImagePath = $mypage->getAttribute("user.edit.old_image_path");
				if(isset($oldImagePath) && strlen($oldImagePath) > 0){
					$imagePath = $oldImagePath;
					$isImageDelete = true;
				}

				//画像を削除する
				if($isImageDelete === true && strlen($imagePath) > 0){
					$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");
					$userLogic->deleteFile($imagePath, $user->getId());
					$mypage->clearAttribute("user.edit.old_image_path");
				}

				$mypage->clearUserInfo();
				$this->jump("edit/complete");

			}

			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$mypage->setAttribute("user.edit.use_session_user_info", true);
				$this->jump("edit");
			}
		}
	}

	function __construct(){
		$mypage = $this->getMyPage();
		if(!$mypage->getIsLoggedIn()) $this->jump("login");

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		$user = $mypage->getUserInfo();
		if(is_null($user)) $this->jump("edit"); //直接URLを入力された場合は、入力フォームに戻す

		parent::__construct();

		//顧客情報フォーム
		$this->buildForm($user, $mypage, UserComponent::MODE_CUSTOM_CONFIRM);

		/**
    	 * ユーザカスタムフィールド
    	 */
    	SOYShopPlugin::load("soyshop.user.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "confirm",
			"app" => $mypage
		));

		$forms = array();
		if(is_array($delegate->getConfirm())){
			foreach($delegate->getConfirm() as $list){
				if(is_array($list)){
					foreach($list as $key => $array){
						$forms[$key] = $array;
					}
				}
			}
		}

		$this->addModel("has_user_customfield", array(
			"visible" => count($forms) > 0,
		));

		$this->createAdd("user_customfield_list", "UserEditCustomfieldConfirm", array(
			"list" => $forms
		));

    	//各項目をcreateAdd
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "build_named_form",
			"app" => $mypage,
			"pageObj" => $this,
			"userId" => $user->getId()
		));

		$mypage->save();//ConfirmPageのMyPageLogicで上書き
	}
}

class UserEditCustomfieldConfirm extends HTMLList{

	protected function populateItem($entity, $key, $counter, $length){

		$this->addLabel("customfield_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("customfield_confirm", array(
			"html" => (isset($entity["confirm"])) ? $entity["confirm"] : ""
		));
	}
}
