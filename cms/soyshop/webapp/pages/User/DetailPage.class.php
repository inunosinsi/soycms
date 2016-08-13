<?php
SOYShopPlugin::load("soyshop.point");
class DetailPage extends WebPage{

	var $id;

	public $component;
	public $backward;

	function doPost(){
		if(!soy2_check_token()){
			SOY2PageController::jump("User.Detail." . $this->id);
		}

		$detail = $_POST["Customer"];
		$detail = $this->component->adjustUser($detail);
//		$custom = $detail["custom"];
		$detail = (object)$detail;
		if(isset($_POST["Address"])){
			$address = $_POST["Address"];
		}else{
			$address = array();
		}

		//メール
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//元のデータを読み込む：readonlyな値をからの値で上書きしないように
		$user = $dao->getById($this->id);
		
		//ログインIDの重複チェック
		if(strlen($_POST["Customer"]["accountId"])){
			//重複している場合は元の値を上書き
			try{
				$oldUser = $dao->getByAccountIdAndNotId($_POST["Customer"]["accountId"], $this->id);
				$detail->accountId = $user->getAccountId();
			}catch(Exception $e){
				//
			}
		}
		
		SOY2::cast($user, $detail);
		$user->setAddressList($address);
//		$user->setAttributes($custom);
		
		
		//メール配信をしないチェックボックス 値を反転
		$notSend = (isset($_POST["Customer"]["notSend"]) && (int)$_POST["Customer"]["notSend"] > 0) ? 1 : 0;
		$user->setNotSend($notSend);
		
		//プロフィールページを公開するチェックボックス
		$isProfileDisplay = (isset($_POST["Customer"]["isProfileDisplay"]) && (int)$_POST["Customer"]["isProfileDisplay"] > 0) ? 1 : 0;
		$user->setIsProfileDisplay($isProfileDisplay);

		$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");
		
		//プロフィールページ用のアカウントを作成
		if($isProfileDisplay == SOYShop_User::PROFILE_IS_DISPLAY && strlen($user->getProfileId()) === 0){
			$profileId = $userLogic->createProfileId($user);
			$user->setProfileId($profileId);
		}

		//画像の削除
		if(isset($_POST["Delete"]) && $_POST["Delete"] == 1){
			$userLogic->deleteFile($user->getImagePath(), $user->getId());
			$user->setImagePath(null);
		}

		//画像のアップロード
		if(isset($_FILES["image"]["name"]) && preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["image"]["name"])){
			$isResize = SOYShop_DataSets::get("config.mypage.profile_resize", 0);
			$resizeWidth = SOYShop_DataSets::get("config.mypage.profile_resize_width", 120);
			$fileName = $userLogic->uploadFile($_FILES["image"]["name"], $_FILES["image"]["tmp_name"], $this->id, $isResize, $resizeWidth);
			$user->setImagePath($fileName);
		}
		
		try{
			$dao->update($user);
		}catch(Exception $e){
			//
		}

		//ユーザカスタムフィールドの値をセッションに入れる
		SOYShopPlugin::load("soyshop.user.customfield");
		SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "register",
			"userId" => $user->getId()
		));

		if(isset($_POST["Point"])){
			SOYShopPlugin::invoke("soyshop.point", array(
				"userId" => $this->id
			));
		}

		SOYShopPlugin::load("soyshop.operate.credit");
		SOYShopPlugin::invoke("soyshop.operate.credit", array(
			"user" => $user,
			"mode" => "user_detail"
		));

		SOY2PageController::jump("User.Detail." . $this->id . "?updated");
	}

    function __construct($args) {
    	$id = (isset($args[0])) ? $args[0] : null;
    	$this->id = $id;
    	
    	/* 共通コンポーネント */
    	SOY2::import("base.site.classes.SOYShop_UserCustomfieldList");
    	SOY2::import("component.UserComponent");
    	SOY2::import("component.backward.BackwardUserComponent");
    	SOY2::import("logic.cart.CartLogic");
    	SOY2::import("logic.mypage.MyPageLogic");
    	
		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();
		
		//多言語
		MessageManager::addMessagePath("admin");
		
    	WebPage::__construct();

    	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

    	try{
    		$shopUser = $dao->getById($id);
    	}catch(Exception $e){
    		SOY2PageController::jump("User");
    		exit;
    	}

    	//ユーザの画像保存ディレクトリが無い場合は生成する
		$dir = $shopUser->getAttachmentsPath();

		/* フォーム */
    	$this->buildForm($shopUser);		//共通など。
		$this->buildJobForm($shopUser);		//法人
		$this->buildProfileForm($shopUser);	//プロフィール
		$this->buildPointForm($shopUser);	//ポイント
    	$this->buildAddressForm($shopUser);	//お届け先

    	/**
    	 * ユーザカスタムフィールド
    	 */
    	SOYShopPlugin::load("soyshop.user.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "form",
//			"app" => MyPage::getMyPage(),
			"userId" => $id
		));

		$forms = array();
		if(count($delegate->getList()) > 0){
			foreach($delegate->getList() as $list){
				if(is_array($list)){
					foreach($list as $key => $array){
						$forms[$key] = $array;
					}
				}
			}
		}

		DisplayPlugin::toggle("has_user_customfield", (count($forms) > 0));

		$this->createAdd("user_customfield_list", "_common.User.CustomFieldFormListComponent", array(
			"list" => $forms
		));


		//注文
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$count = $orderDao->countByUserIdIsRegistered($id);

		$this->addLabel("order_count", array(
			"text" => $count,
		));
		$this->addLink("order_list_link", array(
			"link" => SOY2PageController::createLink("Order?userId=" . $shopUser->getId())
		));
		$this->addLink("order_register_link", array(
			"link" => SOY2PageController::createLink("Order.Register.User." . $shopUser->getId())
		));
		
		DisplayPlugin::toggle("storage", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("store_user_folder"))));		
		$this->addLabel("storage_url", array(
			"text" => SOY2PageController::createLink("User.Storage." . $shopUser->getId())
		));
   }

	/**
	 * フォーム
	 * @param SOYShop_User $user
	 */
   function buildForm(SOYShop_User $user){
		//共通コンポーネントに移し替え  soyshop/component/UserComponent.class.php buildFrom()

		//以前のフォーム 後方互換
		$this->backward->backwardAdminBuildForm($this, $user);

		//共通フォーム
		$this->component->buildForm($this, $user, null, UserComponent::MODE_CUSTOM_FORM);
		
		
		//更新メッセージ
		DisplayPlugin::toggle("update_message", isset($_GET["updated"]));

    	$this->addForm("detail_form", array(
    		"enctype" => "multipart/form-data"
    	));
		
		/* 表示など */
		
		//ユーザID
    	$this->addLabel("id", array(
    		"text" => $user->getId(),
    	));
		
		//メールアドレス
    	$this->addLabel("mail_address", array(
    		"text" => $user->getMailAddress()
    	));
		
		//メールアドレス 変更リンク
    	$this->addLink("mail_edit_link", array(
    		"link" => SOY2PageController::createLink("User.Edit.Mail." . $this->id)
    	));
		
		//パスワード 登録済みの場合 表示
    	$this->addModel("password_is_registered", array(
    		"visible" => strlen($user->getPassword())
    	));
    	
    	//パスワード 登録していない場合 表示
    	$this->addModel("password_is_not_registered", array(
    		"visible" => (!strlen($user->getPassword()))
    	));
    	
    	//パスワード 変更リンク
    	$this->addLink("password_edit_link", array(
    		"link" => SOY2PageController::createLink("User.Edit.Password." . $this->id)
    	));
		
		//登録日時
    	$this->addLabel("register_date", array(
    		"text" => (is_null($user->getRegisterDate())) ? "" : date("Y-m-d H:i:s", $user->getRegisterDate()),
    	));

		//本登録日時
    	$this->addLabel("real_register_date", array(
    		"text" => (is_null($user->getRealRegisterDate())) ? "" : date("Y-m-d H:i:s", $user->getRealRegisterDate()),
    	));

		//更新日時
    	$this->addLabel("update_date", array(
    		"text" => (is_null($user->getUpdateDate())) ? "" : date("Y-m-d H:i:s", $user->getUpdateDate()),
    	));
    }
	
	/**
	 * 法人関連フォーム
	 * @param SOYShop_User $user
	 */
	function buildJobForm(SOYShop_User $user){
		/* 勤務先 */
		
		//法人名(勤務先など)
    	$this->addInput("office", array(
    		"name" => "Customer[jobName]",
    		"value" => $user->getJobName(),
    		"size" => 60,
    	));

		//法人所在地郵便番号
    	$this->addInput("office_post_number", array(
    		"name" => "Customer[jobZipCode]",
    		"value" => $user->getJobZipCode(),
    		"size" => 60,
    	));

		//法人所在地 都道府県
    	$this->addSelect("office_area", array(
    		"name" => "Customer[jobArea]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => $user->getJobArea()
    	));

		//法人所在地 入力1
    	$this->addInput("office_address1", array(
    		"name" => "Customer[jobAddress1]",
    		"value" => $user->getJobAddress1(),
    		"size" => 40
    	));

		//法人所在地 入力2
    	$this->addInput("office_address2", array(
    		"name" => "Customer[jobAddress2]",
    		"value" => $user->getJobAddress2(),
    		"size" => 100
    	));

		//法人電話番号
    	$this->addInput("office_tel_number", array(
    		"name" => "Customer[jobTelephoneNumber]",
    		"value" => $user->getJobTelephoneNumber(),
    		"size" => 30
    	));

		//法人FAX番号
    	$this->addInput("office_fax_number", array(
    		"name" => "Customer[jobFaxNumber]",
    		"value" => $user->getJobFaxNumber(),
    		"size" => 30
    	));

		/*** カード会員操作 ***/
		SOYShopPlugin::load("soyshop.operate.credit");
		$delegate = SOYShopPlugin::invoke("soyshop.operate.credit", array(
			"user" => $user,
			"mode" => "user_detail",
		));
		$list = $delegate->getList();
		DisplayPlugin::toggle("operate_credit_menu", count($list) > 0);
		
		$this->createAdd("operate_list", "_common.User.OperateListComponent", array(
			"list" => $list
		));
	}
	
	/**
	 * プロフィール関連フォーム
	 * @param SOYShop_User $user
	 */
	function buildProfileForm(SOYShop_User $user){
		
	}
	
	/**
	 * お届け先フォーム
	 * @param SOYShop_User $user
	 */
	function buildAddressForm(SOYShop_User $user){

		$this->createAdd("address_list", "_common.User.AddressListComponent", array(
			"list" => $user->getAddressListArray()
		));
	}
	
	/**
	 * ポイントフォーム
	 * @param SOYShop_User $user
	 */
	function buildPointForm(SOYShop_User $user){

		//ポイント
    	$activedPointPlugin = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_point_base")));
    	DisplayPlugin::toggle("point", $activedPointPlugin);
    	DisplayPlugin::toggle("point2", $activedPointPlugin);
		
		//ポイントプラグインを無効にしていても下記の処理は行う
		
		/* ここ以下はポイント有効時 */
		SOY2::imports("module.plugins.common_point_base.domain.*");
		
		$this->addInput("point", array(
    		"name" => "Point",
    		"value" => $user->getPoint(),
    		"style" => "ime-mode:inactive;"
    	));

    	$timeLimit = $this->getTimeLimit($user->getId());
    	$this->addLabel("time_limit", array(
    		"text" => (isset($timeLimit)) ? date("Y-m-d H:i:s", $timeLimit) : "無期限"
    	));
    	
    	$histories = $this->getPointHistories($user->getId());
    	
    	DisplayPlugin::toggle("point_history", (count($histories) > 0));
    	
    	$this->createAdd("point_history_list", "_common.User.PointHistoryListComponent", array(
    		"list" => $histories
    	));
	}

	function getTimeLimit($userId){
		$delegate = SOYShopPlugin::invoke("soyshop.point", array(
			"userId" => $userId
		));
		return $delegate->getTimeLimit();
	}
	
	function getPointHistories($userId){
		$historyDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		$historyDao->setLimit(10);	//上位10件
		try{
			$histories = $historyDao->getByUserId($userId);
		}catch(Exception $e){
			$histories = array();
		}
		return $histories;
	}
	
	function getCSS(){
		return array("./css/admin/user_detail.css");
	}
	
}
?>