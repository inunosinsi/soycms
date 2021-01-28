<?php
SOYShopPlugin::load("soyshop.point");
class DetailPage extends WebPage{

	var $id;

	public $component;
	public $backward;

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(!soy2_check_token()){
			SOY2PageController::jump("User.Detail." . $this->id);
		}

		//メール
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//元のデータを読み込む：readonlyな値をからの値で上書きしないように
		$user = soyshop_get_user_object($this->id);

		if(isset($_POST["Customer"])){
			$detail = $_POST["Customer"];
			$detail = $this->component->adjustUser($detail);
	//		$custom = $detail["custom"];
			$detail = (object)$detail;
			if(isset($_POST["Address"])){
				$address = $_POST["Address"];
			}else{
				$address = array();
			}

			//ログインIDの重複チェック
			if(isset($_POST["Customer"]["accountId"]) && strlen($_POST["Customer"]["accountId"])){
				//重複している場合は元の値を上書き
				try{
					$oldUser = $dao->getByAccountIdAndNotId($_POST["Customer"]["accountId"], $this->id);
					$detail->accountId = $user->getAccountId();
				}catch(Exception $e){
					//
				}
			}

			$oldCode = $user->getUserCode();	//更新前の顧客コードの取得

			SOY2::cast($user, $detail);
			$user->setAddressList($address);
	//		$user->setAttributes($custom);

			$newCode = $user->getUserCode();	//新たに入力した顧客コードを取得

			//指定の顧客コードは既に登録されているか？
			if(SOYShop_ShopConfig::load()->getUseUserCode()){
				try{
					$confirmUser = $dao->getByUserCode($newCode);
					$user->setUserCode($oldCode);	//顧客コードを戻して更新
					if($confirmUser->getId() != $user->getId()){	//確認用に取得したユーザとIDが異なる場合のみエラー
						SOY2ActionSession::getUserSession()->setAttribute("user_code_error", 1);
					}
				}catch(Exception $e){
					//
				}
			}


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
			if(isset($_FILES["image"]["name"]) && preg_match('/(jpg|jpeg|gif|png)$/i', $_FILES["image"]["name"])){
				$isResize = SOYShop_DataSets::get("config.mypage.profile_resize", 0);
				$resizeWidth = SOYShop_DataSets::get("config.mypage.profile_resize_width", 120);
				$fileName = $userLogic->uploadFile($_FILES["image"]["name"], $_FILES["image"]["tmp_name"], $this->id, $isResize, $resizeWidth);
				$user->setImagePath($fileName);
			}

			//管理画面から本登録にする
			if($user->getUserType() != SOYShop_User::USERTYPE_REGISTER && isset($_POST["real_register"])){
				$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
				$user->setRealRegisterDate(time());
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

			if(isset($_POST["Point"]) || isset($_POST["Ticket"])){
				SOYShopPlugin::invoke("soyshop.point", array(
					"userId" => $this->id
				));
			}
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
		SOY2::import("domain.config.SOYShop_ShopConfig");
    	SOY2::import("base.site.classes.SOYShop_UserCustomfieldList");
    	SOY2::import("component.UserComponent");
    	SOY2::import("component.backward.BackwardUserComponent");
    	SOY2::import("logic.cart.CartLogic");
    	SOY2::import("logic.mypage.MyPageLogic");

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		//多言語
		MessageManager::addMessagePath("admin");

    	parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		//詳細ページを開いた時に何らかの処理をする
		SOYShopPlugin::load("soyshop.user");
		SOYShopPlugin::invoke("soyshop.user", array(
			"mode" => "detail",
			"userId" => $this->id
		));

		DisplayPlugin::toggle("sended", isset($_GET["sended"]));

    	$shopUser = soyshop_get_user_object($id);
		if(is_null($shopUser->getId())) SOY2PageController::jump("User");

		//カートIDとマイページIDがnoneの場合は公開側からの注文ボタンを表示しない
		DisplayPlugin::toggle("order_cart_link", (soyshop_get_cart_id() != "none" && soyshop_get_mypage_id() != "none"));

		//管理画面から注文ボタン
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$isDisplayOrderButton = SOYShop_ShopConfig::load()->getDisplayOrderButtonOnUserAdminPage();
		DisplayPlugin::toggle("orderable_button", $isDisplayOrderButton);

		//例外：注文関連ボタンを表示しない設定だけれども、マイページが有効の場合はログインボタンを出力する
		DisplayPlugin::toggle("log_in_button", (!$isDisplayOrderButton && soyshop_get_mypage_id() != "none"));

    	//ユーザの画像保存ディレクトリが無い場合は生成する
		$dir = $shopUser->getAttachmentsPath();

		DisplayPlugin::toggle("notice_tmp_register", ($shopUser->getUserType() != SOYShop_User::USERTYPE_REGISTER));
		DisplayPlugin::toggle("notice_no_publish", ($shopUser->getIsPublish() != SOYShop_User::USER_IS_PUBLISH));

		$session = SOY2ActionSession::getUserSession();
		$userCodeError = $session->getAttribute("user_code_error");
		DisplayPlugin::toggle("user_code_error", isset($userCodeError));
		$session->setAttribute("user_code_error", null);

		//タイトルの箇所にあるボタン
		SOYShopPlugin::load("soyshop.user.button");
		$buttons = SOYShopPlugin::invoke("soyshop.user.button", array(
			"userId" => $id
		))->getButtons();

		$this->createAdd("user_title_button_list", "_common.User.TitleButtonListComponent", array(
			"list" => $buttons
		));

		/* フォーム */
    	self::_buildForm($shopUser);		//共通など。
		self::_buildJobForm($shopUser);		//法人
		self::_buildProfileForm($shopUser);	//プロフィール
		self::_buildAddressForm($shopUser);	//お届け先
		self::_buildPointForm($shopUser);	//ポイント
		self::_buildTicketForm($shopUser);	//チケット

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
		list($count, $cancelCount) = self::_getOrderCountByUserId($id);

		$this->addLabel("order_count", array(
			"text" => $count,
		));
		DisplayPlugin::toggle("cancel_count", $cancelCount);
		$this->addLabel("order_cancel_count", array(
			"text" => $cancelCount
		));
		// $this->addLink("order_list_link", array(
		// 		"link" => ( is_numeric($count) && isset($order) )
		// 		? SOY2PageController::createLink("Order.Detail.".$order->getId())
		// 		: SOY2PageController::createLink("Order?search[userId]=" . $shopUser->getId()),
		// ));
		$this->addLink("order_list_link", array(
			"link" => SOY2PageController::createLink("Order?search[userId]=" . $shopUser->getId()),
			"visible" => AUTH_ADMINORDER
		));
		$this->addLink("order_register_link", array(
			"link" => SOY2PageController::createLink("Order.Register.User." . $shopUser->getId()),
			"visible" => AUTH_ADMINORDER
		));
		$this->addLink("order_cart_link", array(
			"link" => soyshop_get_mypage_url(true) . "/login?purchase=proxy&user_id=" . $shopUser->getId(),
			"visible" => AUTH_ADMINORDER,
			"target" => "_blank"
		));

		$this->addLink("mypage_login_link", array(
			"link" => soyshop_get_mypage_url(true) . "/login?purchase=proxy&user_id=" . $shopUser->getId(),
			"visible" => AUTH_ADMINORDER,
			"target" => "_blank"
		));

		DisplayPlugin::toggle("storage", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("store_user_folder"))));
		$this->addLabel("storage_url", array(
			"text" => SOY2PageController::createLink("User.Storage." . $shopUser->getId())
		));

		$this->addModel("zip2address_js", array(
			"src" => soyshop_get_zip_2_address_js_filepath()
		));
   }

   private function _getOrderCountByUserId($userId){
	   $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	   try{
		   $count = $dao->countByUserIdIsRegistered($userId);
		   $cancelCount = $dao->countByUserIdIsCanceled($userId);
	   }catch(Exception $e){
		   $count = 0;
		   $cancelCount = 0;
	   }
	   return array($count, $cancelCount);
   }

	/**
	 * フォーム
	 * @param SOYShop_User $user
	 */
   private function _buildForm(SOYShop_User $user){
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
    		"link" => SOY2PageController::createLink("User.Edit.Mail." . $this->id),
			"visible" => AUTH_ADMINORDER
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
    		"link" => SOY2PageController::createLink("User.Edit.Password." . $this->id),
			"visible" => AUTH_ADMINORDER
    	));

		//登録日時
    	$this->addLabel("register_date", array(
    		"text" => (is_null($user->getRegisterDate())) ? "" : date("Y-m-d H:i:s", $user->getRegisterDate()),
    	));

		//本登録日時
    	$this->addLabel("real_register_date", array(
    		"text" => (is_null($user->getRealRegisterDate())) ? "" : date("Y-m-d H:i:s", $user->getRealRegisterDate()),
    	));

    	//管理画面から本登録できるようにする
    	DisplayPlugin::toggle("display_real_register", ($user->getUserType() != SOYShop_User::USERTYPE_REGISTER));
    	$this->addCheckBox("real_register_check", array(
    		"name" => "real_register",
    		"value" => 1,
    		"label" => "本登録にする"
    	));

		//更新日時
    	$this->addLabel("update_date", array(
    		"text" => (is_null($user->getUpdateDate())) ? "" : date("Y-m-d H:i:s", $user->getUpdateDate()),
    	));

		//住所の下に拡張フォーム
		SOYShopPlugin::load("soyshop.user.address");
		$forms = SOYShopPlugin::invoke("soyshop.user.address", array(
			"userId" => $user->getId()
		))->getForm();

		$this->createAdd("advenced_address_list", "_common.User.AdvancedAddressListComponent", array(
			"list" => $forms
		));

		SOY2::import("util.SOYShopPluginUtil");
		DisplayPlugin::toggle("user_custom_search_field", SOYShopPluginUtil::checkIsActive("user_custom_search_field"));

		//項目の非表示用タグ
		foreach(SOYShop_ShopConfig::load()->getCustomerAdminConfig() as $key => $bool){
			if($key == "accountId" && $bool){
				//ログインIDのみ、マイページでログインIDを使用する時だけtrueにする
				$bool = (SOYShop_ShopConfig::load()->getAllowLoginIdLogin() != 0);
			}
			DisplayPlugin::toggle($key, $bool);
		}
    }

	/**
	 * 法人関連フォーム
	 * @param SOYShop_User $user
	 */
	private function _buildJobForm(SOYShop_User $user){
		/* 勤務先 */
		DisplayPlugin::toggle("office_items", SOYShop_ShopConfig::load()->getDisplayUserOfficeItems());

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
	}

	/**
	 * プロフィール関連フォーム
	 * @param SOYShop_User $user
	 */
	private function _buildProfileForm(SOYShop_User $user){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("profile_items", SOYShop_ShopConfig::load()->getDisplayUserProfileItems());

		DisplayPlugin::toggle("profile_confirm", $user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY);
		$this->addLink("profile_confirm_link", array(
			"link" => soyshop_get_mypage_url() . "/profile/" . $user->getProfileId(),
			"target" => "_blank"
		));
	}

	/**
	 * お届け先フォーム
	 * @param SOYShop_User $user
	 */
	private function _buildAddressForm(SOYShop_User $user){
		$this->createAdd("address_list", "_common.User.AddressListComponent", array(
			"list" => $user->getAddressListArray()
		));
	}

	/**
	 * ポイントフォーム
	 * @param SOYShop_User $user
	 */
	private function _buildPointForm(SOYShop_User $user){
		//ポイント
    	$activedPointPlugin = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_point_base")));
    	DisplayPlugin::toggle("point", $activedPointPlugin);

		list($point, $timeLimit) = ($activedPointPlugin) ? self::_getPointAndTimeLimitByUserId($user) : array(0, null);

		//ポイントプラグインを無効にしていても下記の処理は行う
		$this->addInput("point", array(
			"name" => "Point",
			"value" => $point,
			"style" => "ime-mode:inactive;"
		));

		$this->addLabel("time_limit", array(
			"text" => (is_numeric($timeLimit)) ? date("Y-m-d H:i:s", $timeLimit) : "無期限"
		));
	}

	private function _getPointAndTimeLimitByUserId(SOYShop_User $user){
		SOY2::imports("module.plugins.common_point_base.domain.*");
		$point = $user->getPoint();
		$timeLimit = SOYShopPlugin::invoke("soyshop.point", array("userId" => $user->getId()))->getTimeLimit();
		return array($point, $timeLimit);
	}

	/**
	 * チケットフォーム
	 * @param SOYShop_User $user
	 */
	private function _buildTicketForm(SOYShop_User $user){
		//チケット
    	$activedTicketPlugin = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_ticket_base")));
    	DisplayPlugin::toggle("ticket", $activedTicketPlugin);

		/* ここ以下はチケット有効時 */
		if($activedTicketPlugin){
			$count = self::_getTicketCountByUserId($user->getId());
			SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
			$config = TicketBaseUtil::getConfig();
			$label = $config["label"];
			$unit = $config["unit"];
		}else{
			$count = 0;
			$label = "チケット";
			$unit = "枚";
		}

		//チケットプラグインを無効にしていても下記の処理は行う
		$this->addLabel("ticket_label", array(
			"text" => $label
		));

		$this->addLabel("ticket_unit", array(
			"text" => $unit
		));

		$this->addInput("ticket_count", array(
    		"name" => "Ticket",
    		"value" => $count,
    		"style" => "ime-mode:inactive;"
    	));
	}

	private function _getTicketCountByUserId($userId){
		SOY2::imports("module.plugins.common_ticket_base.domain.*");
		try{
			return SOY2DAOFactory::create("SOYShop_TicketDAO")->getByUserId($userId)->getCount();
		}catch(Exception $e){
			return 0;
		}
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build(SHOP_USER_LABEL . "情報詳細", array("User" => SHOP_USER_LABEL . "管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("User.FooterMenu.DetailFooterMenuPage", array(
				"arguments" => array($this->id)
			))->getObject();
		}catch(Exception $e){
			return null;
		}
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}

	// function getCSS(){
	// 	//$root = SOY2PageController::createRelativeLink("./js/");
	// 	return array(
	// 		"./css/admin/user_detail.css",
	// 		//$root . "tools/soy2_date_picker.css"
	// 	);
	// }
}
