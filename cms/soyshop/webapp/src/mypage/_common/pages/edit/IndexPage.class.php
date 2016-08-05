<?php
class IndexPage extends MainMyPagePageBase{

	private $id;
	public $component;
	public $backward;


	function doPost(){

		$mypage = MyPageLogic::getMyPage();

		//ユーザカスタムフィールドの値をセッションに入れる
		if(isset($_POST["user_customfield"])){	
			SOYShopPlugin::load("soyshop.user.customfield");
			SOYShopPlugin::invoke("soyshop.user.customfield", array(
				"mode" => "post",
				"app" => $mypage,
				"param" => $_POST["user_customfield"]
			));
		}

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
			$anchor = "zipcode1";

			$mypage->setUserInfo($user);
			$mypage->save();

			$this->jump("edit#" . $anchor);
			exit;
		}

		//保存
		if(soy2_check_token()){

			if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){

				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $this->getUser();
				
				//POSTデータ
				//名前関連のデータの文字列変換
				$customer = $_POST["Customer"];
				$customer = $this->component->adjustUser($customer);

				$postUser = (object)$customer;
				$user = SOY2::cast($user, $postUser);
				
				$isProfileDisplay = (isset($_POST["Customer"]["isProfileDisplay"]) && (int)$_POST["Customer"]["isProfileDisplay"] > 0);
				$user->setIsProfileDisplay($isProfileDisplay);

				$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");
		
				//プロフィールページ用のアカウントを作成
				if($isProfileDisplay && strlen($user->getProfileId()) === 0){
					$profileId = $userLogic->createProfileId($user);
					$user->setProfileId($profileId);
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
				$mypage->setUserInfo($user);
				$mypage->setAttribute("user.edit.use_session_user_info", true);
				
				if( $this->checkError($mypage) ){
					$this->jump("edit/confirm");
				}else{
					$this->jump("edit");
				}
			}
		}

	}

	function __construct(){

		$mypage = MyPageLogic::getMyPage();
		
		//ログインしていなかったら飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

		$user = $mypage->getUserInfo();
		if(is_null($user) || !$mypage->getAttribute("user.edit.use_session_user_info")){
			$user = $this->getUser();
		}

		$this->id = $this->getUserId();

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		WebPage::WebPage();

		//顧客情報フォーム
		$this->buildForm($user, $mypage);

		//エラー周り
		DisplayPlugin::toggle("has_error", $mypage->hasError());
		$this->appendErrors($mypage);

		//使用済みのセッション値をクリア
		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->setAttribute("user.edit.use_session_user_info", null);
		$mypage->save();
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
			"mypage" => $mypage,
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
	function checkError(MyPageLogic $mypage){
		$user = $mypage->getUserInfo();
		$mypage->clearErrorMessage();
		$res = true;
		
		//共通エラーチェック
		$res = $this->component->checkError($user, $mypage, UserComponent::MODE_MYPAGE_EDIT);

		$mypage->save();
		return $res;
	}
}
?>