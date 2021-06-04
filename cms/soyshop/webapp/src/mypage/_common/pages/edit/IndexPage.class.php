<?php
class IndexPage extends MainMyPagePageBase{

	private $id;
	public $component;
	public $backward;


	function doPost(){

		$mypage = $this->getMyPage();

		//ユーザカスタムフィールドの値をセッションに入れる
		// if(isset($_POST["user_customfield"])){
		// 	SOYShopPlugin::load("soyshop.user.customfield");
		// 	SOYShopPlugin::invoke("soyshop.user.customfield", array(
		// 		"mode" => "post",
		// 		"app" => $mypage,
		// 		"param" => $_POST["user_customfield"]
		// 	));
		// }

		//郵便番号での住所検索
		if(isset($_POST["user_zip_search"]) || isset($_POST["user_zip_search_x"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");

			$user = $this->getUser();
			$postUser = (object)$_POST["Customer"];
			$custom = $_POST["Customer"]["custom"];
			$user = SOY2::cast($user, $postUser);
			$user->setAttributes($custom);

			$code = soyshop_cart_address_validate($user->getZipcode());
			$res = $logic->search($code);
			$user->setArea(SOYShop_Area::getAreaByText($res["prefecture"]));
			$user->setAddress1($res["address1"]);
			$user->setAddress2($res["address2"]);
			$user->setAddress3($res["address3"]);
			$anchor = "zipcode1";

			$mypage->setUserInfo($user);
			$mypage->save();

			$this->jump("edit#" . $anchor);
			exit;
		}

		//保存
		if(soy2_check_token() && soy2_check_referer()){

			if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){

				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $this->getUser();

				//POSTデータ
				//名前関連のデータの文字列変換
				$customer = $_POST["Customer"];
				$customer = $this->component->adjustUser($customer);

				$postUser = (object)$customer;
				$user = SOY2::cast($user, $postUser);

				$isProfileDisplay = (isset($_POST["Customer"]["isProfileDisplay"]) && (int)$_POST["Customer"]["isProfileDisplay"] > 0) ? 1 : 0;
				$user->setIsProfileDisplay($isProfileDisplay);

				$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");

				//プロフィールページ用のアカウントを作成
				if($isProfileDisplay && strlen($user->getProfileId()) === 0){
					$user->setProfileId($userLogic->createProfileId($user));
				}

				//画像の削除
				if(isset($_POST["Delete"]) && (int)$_POST["Delete"] === 1){
					$mypage->setAttribute("user.edit.delete_image", true);
				}else{
					$mypage->setAttribute("user.edit.delete_image", false);
				}

				//画像のアップロード
				if(isset($_FILES["image"]["name"]) && preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["image"]["name"])){

					//既に画像を登録してある場合は、新しい画像を登録後に古い画像を削除する
					if(!is_null($user->getImagePath()) && strlen($user->getImagePath()) > 0){
						$mypage->setAttribute("user.edit.old_image_path", $user->getImagePath());
					}

					$isResize = SOYShop_DataSets::get("config.mypage.profile_resize", 0);
					$resizeWidth = SOYShop_DataSets::get("config.mypage.profile_resize_width", 120);
					$fileName = $userLogic->uploadFile($_FILES["image"]["name"], $_FILES["image"]["tmp_name"], $this->id, $isResize, $resizeWidth);
					$user->setImagePath($fileName);
				}

				//ユーザIDの変更を不許可
				$user->setId($this->id);

				//ユーザカスタムフィールドの値をセッションに入れる
				if(isset($_POST["user_customfield"]) || isset($_POST["user_custom_search"])){
					SOYShopPlugin::load("soyshop.user.customfield");
					SOYShopPlugin::invoke("soyshop.user.customfield", array(
						"mode" => "post",
						"app" => $mypage,
						"param" => $_POST["user_customfield"]
					));
				}

				$mypage->setUserInfo($user);
				$mypage->setAttribute("user.edit.use_session_user_info", true);
				$mypage->save();

				if( self::checkError($mypage) ){
					$this->jump("edit/confirm");
				}else{
					$this->jump("edit");
				}
			}
		}

	}

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		$mypage = $this->getMyPage();
		$user = $mypage->getUserInfo();
		if(is_null($user) || !$mypage->getAttribute("user.edit.use_session_user_info")){
			$user = $this->getUser();
			$isEditingData = false;
		}else{
			$isEditingData = true;
		}

		$this->id = $this->getUserId();

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		parent::__construct();

		//顧客情報フォーム
		$this->buildForm($user, $mypage);

		//編集中の注意
		DisplayPlugin::toggle("is_editing", $isEditingData);

		//エラー周り
		DisplayPlugin::toggle("has_error", $mypage->hasError());
		$this->appendErrors($mypage);

		//使用済みのセッション値をクリア confirmを見ている時は削除しない
		if(!strpos($_SERVER["REQUEST_URI"], "/edit/confirm")){
			$mypage->clearUserInfo();
			$mypage->clearErrorMessage();
			$mypage->setAttribute("user.edit.use_session_user_info", null);
			$this->clearCustomFieldValue($mypage);
			$mypage->save();
		}

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}

	/**
	 * カスタムフィールドの編集中の値を削除する
	 * @param unknown $mypage
	 */
	protected function clearCustomFieldValue($mypage){
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
				"mode" => "clear",
				"app" => $mypage,
				"userId" => $this->id,
		));
	}

	/**
	 * @param SOYShop_User $user
	 * @param MyPageLogic $mypage
	 * @param string $mode ユーザカスタムフィールドのモード指定
	 */
	function buildForm(SOYShop_User $user, MyPageLogic $mypage, $mode=UserComponent::MODE_CUSTOM_FORM){

		//共通コンポーネントに移し替え  soyshop/component/UserComponent.class.php buildFrom()
		//後方互換性確保は soyshop/component/backward/BackwardUserComponent

		//以前のフォーム 後方互換
		$this->backward->backwardMyPageEdit($this, $user);

		//共通フォーム
		$this->component->buildForm($this, $user, $mypage, $mode);

		//各項目をcreateAdd
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "build_named_form",
			"app" => $mypage,
			"pageObj" => $this,
			"userId" => $user->getId()
		));
	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors(MyPageLogic $mypage){
		//共通エラーメッセージ
		$this->component->appendErrors($this, $mypage);
	}

	/**
	 * 入力された値のチェック。エラーがなければtrueを返す
	 * @return boolean
	 */
	private function checkError(MyPageLogic $mypage){
		$user = $mypage->getUserInfo();
		$mypage->clearErrorMessage();
		$res = true;

		//共通エラーチェック
		$res = $this->component->checkError($user, $mypage, UserComponent::MODE_MYPAGE_EDIT);

		$mypage->save();
		return $res;
	}
}
