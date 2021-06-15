<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class ShopConfigPage extends WebPage{

	private $config;

	function doPost(){
		$config = $this->config;

		foreach(array(
			"isTrailingSlash" => 0,
			"isDomainWww" => 0,
			"consumptionTaxInclusivePricingRate" => SOYShop_ShopConfig::CONSUMPTION_TAX_RATE,
			"consumptionTaxInclusiveCommission" => 0,
			"isOrderListOneYearsWonth" => 0,
			"displayStockCount" => 0,
			"ignoreStock" => 0,
			"isHiddenStockCount" => 0,
			"searchChildItemOnListPage" => 0,
			"searchChildItemOnDetailPage" => 0,
			"displayPageAfterLogout" => 0,
			"displaySendInformationForm" => 0,
			"allowMailAddressLogin" => 0,
			"allowLoginIdLogin" => 0,
			"passwordCount" => 8,
			"displayUsableTagList" => 0,
			"useUserCode" => 0,
			"insertDummyMailAddress" => 0,
			"insertDummyMailAddressOnAdmin" => 0,
			"insertDummyMailAddressOnAdminRegister" => 0,
			"insertDummyAddressOnAdmin" => 0,
			//"isChildItemOnAdminOrder" => 0,
			"isUnregisteredItem" => 0,
			"displayRegisterAfterItemSearchOnAdmin" => 0,
			"addSearchChildItemNameOnAdmin" => 0,
			"allowRegistrationZeroYenProducts" => 0,
			"allowRegistrationZeroQuantityProducts" => 0,
			"changeParentItemNameOnAdmin" => 0,	//管理画面で子商品名を親商品名(商品コードも含む)に変換する
			"displayPurchasePriceOnAdmin" => 0,
			"displayOrderAdminPage" => 0,
			"displayItemAdminPage" => 0,
			"displayUserAdminPage" => 0,
			"displayOrderButtonOnUserAdminPage" => 0,
			"defalutArea" => 0,
			"displayUserOfficeItems" => 0,
			"displayUserProfileItems" => 0,
			"insertDummyItemCode" => 0,
			"displayItemKeywords" => 0,
			"displayItemDescription" => 0,
			"displayItemImage" => 0
		) as $key => $null){
			$_POST["Config"][$key] = (isset($_POST["Config"][$key])) ? (int)$_POST["Config"][$key] : $null;
		}

		if(!isset($_POST["Config"]["consumptionTaxModule"])) $_POST["Config"]["consumptionTaxModule"] = null;

		$consumptionTax = (isset($_POST["Config"]["consumptionTax"])) ? $_POST["Config"]["consumptionTax"] : null; //外税
		$taxInclusive = (isset($_POST["Config"]["consumptionTaxInclusivePricing"])) ? $_POST["Config"]["consumptionTaxInclusivePricing"] : null; //内税

		//外税と内税に同時にチェックが入っていた場合は内税を非表示にする
		if($consumptionTax && $taxInclusive){
			$_POST["Config"]["consumptionTaxInclusivePricing"] = null;
		}

		SOY2::cast($config, (object)$_POST["Config"]);
		SOYShop_ShopConfig::save($config);

		SOY2PageController::jump("Config.ShopConfig?updated");
	}

	function __construct() {
		$this->config = SOYShop_ShopConfig::load();

		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		error_reporting(E_ALL ^ E_NOTICE);
		$this->buildForm();

		$this->addLink("return_link", array(
			"link" => SOY2PageController::createLink("Config")
		));
	}

	function buildForm(){
		$config = $this->config;

		$this->addForm("update_form");

		/*** ショップの設定 ***/
		$this->addInput("shop_name", array(
			"name" => "Config[shopName]",
			"value" => $config->getShopName()
		));

		$this->addInput("site_url", array(
			"name" => "Config[siteUrl]",
			"value" => $config->getSiteUrl()
		));

		$company = $config->getCompanyInformation();
		if(!isset($company["building"])) $company["building"] = "";

		foreach($company as $key => $value){
			$this->addInput("company_" . $key, array(
				"name" => "Config[companyInformation][$key]",
				"value" => $value
			));
		}

		//アプリの設定
		$this->addInput("appName", array(
			"name" => "Config[appName]",
			"value" => $config->getAppName()
		));

		$this->addInput("appLogoPath", array(
			"name" => "Config[appLogoPath]",
			"value" => $config->getAppLogoPath()
		));

		//SOY Appのロゴ画像のパス
		$this->addLabel("appLogoPathSample", array(
			"text" => SOYAppUtil::getSOYAppLogoPath()
		));

		//カノニカルURL
		$this->addCheckBox("is_trailing_slash", array(
			"name" => "Config[isTrailingSlash]",
			"value" => 1,
			"selected" => ($config->getIsTrailingSlash() == 1),
			"label" => "あり"
		));

		$this->addCheckBox("no_trailing_slash", array(
			"name" => "Config[isTrailingSlash]",
			"value" => 0,
			"selected" => ($config->getIsTrailingSlash() == 0),
			"label" => "なし"
		));

		$this->addCheckBox("is_domain_www", array(
			"name" => "Config[isDomainWww]",
			"value" => 1,
			"selected" => ($config->getIsDomainWww() == 1),
			"label" => "あり"
		));

		$this->addCheckBox("no_domain_www", array(
			"name" => "Config[isDomainWww]",
			"value" => 0,
			"selected" => ($config->getIsDomainWww() == 0),
			"label" => "なし"
		));

		//消費税別表示モード
		DisplayPlugin::toggle("consumption_tax_inclusive_pricing_mode_off", (!$config->getConsumptionTaxInclusivePricing()));

		$this->addCheckBox("consumptionTax", array(
			"selected" => $config->getConsumptionTax(),
			"value" => 1,
			"name" => "Config[consumptionTax]",
			"label" => "税別価格(外税)を表示する",
			"isBoolean" => 1
		));

		$taxModuleList = self::getTaxModuleList();
		DisplayPlugin::toggle("no_tax_module_list", (count($taxModuleList) === 0));
		DisplayPlugin::toggle("is_tax_module_list", (count($taxModuleList) > 0));

		$this->addSelect("taxModuleList", array(
			"name" => "Config[consumptionTaxModule]",
			"options" => $taxModuleList,
			"selected" => true
		));

		$taxModuleLink = self::buildTaxModuleLink($config->getConsumptionTaxModule());
		$this->addLink("taxModuleLink", array(
			"link" => $taxModuleLink,
			"visible" => (strlen($taxModuleLink) > 0)
		));

		DisplayPlugin::toggle("consumption_tax_mode_off", (!$config->getConsumptionTax()));

		//内税
		$this->addCheckBox("consumptionTaxInclusivePricing", array(
			"selected" => $config->getConsumptionTaxInclusivePricing(),
			"value" => 1,
			"name" => "Config[consumptionTaxInclusivePricing]",
			"label" => "内税を表示する",
			"isBoolean" => 1
		));

		$this->addCheckBox("consumptionTaxInclusivePricingRate", array(
			"name" => "Config[consumptionTaxInclusivePricingRate]",
			"value" => $config->getConsumptionTaxInclusivePricingRate(),
			"style" => "width:5%;text-align:right;ime-mode:inactive;"
		));

		$this->addCheckBox("consumptionTaxInclusiveCommission", array(
			"name" => "Config[consumptionTaxInclusiveCommission]",
			"value" => 1,
			"selected" => $config->getConsumptionTaxInclusiveCommission(),
			"label" => "送料や手数料も消費税の課税対象に含める",
		));


		//SSL設定
		$this->addSelect("sslConfig", array(
			"name" => "Config[sslConfig]",
			"options" => SOYShop_ShopConfig::getSSLConfigList(),
			"selected" => $config->getSSLConfig()
		));

		$this->addCheckBox("isOrderListOneYearsWonth", array(
			"name" => "Config[isOrderListOneYearsWonth]",
			"value" => 1,
			"selected" => $config->getIsOrderListOneYearsWonth(),
			"label" => "注文一覧の標準表示範囲を一年にする"
		));

		//在庫商品通知モード
		$this->addCheckBox("displayStock", array(
			"selected" => $config->getDisplayStock(),
			"value" => 1,
			"name" => "Config[displayStock]",
			"label" => "表示する",
			"isBoolean" => 1
		));
		$this->addInput("displayStockCount", array(
			"name" => "Config[displayStockCount]",
			"value" => $config->getDisplayStockCount()
		));

		//マイページログイン
		$this->addCheckBox("allowMailAddressLogin", array(
			"name" => "Config[allowMailAddressLogin]",
			"value" => 1,
			"selected" => $config->getAllowMailAddressLogin(),
			"label" => "メールアドレスでログインを許可する",
		));

		$this->addCheckBox("allowLoginIdLogin", array(
			"name" => "Config[allowLoginIdLogin]",
			"value" => 1,
			"selected" => $config->getAllowLoginIdLogin(),
			"label" => "ログインIDでログインを許可する",
		));

		$this->addInput("accountIdItemName", array(
			"name" => "Config[accountIdItemName]",
			"value" => $config->getAccountIdItemName()
		));

		$this->addInput("passwordCount", array(
			"name" => "Config[passwordCount]",
			"value" => $config->getPasswordCount(),
			"style" => "width:60px;"
		));

		//在庫無視モード
		$this->addCheckBox("ignoreStock", array(
			"selected" => $config->getIgnoreStock(),
			"value" => 1,
			"name" => "Config[ignoreStock]",
			"label" => "無視する",
			"isBoolean" => 1
		));

		$this->addCheckBox("isHiddenStockCount", array(
			"selected" => $config->getIsHiddenStockCount(),
			"value" => 1,
			"name" => "Config[isHiddenStockCount]",
			"label" => "在庫数を無視している時、商品毎の在庫数を---にする",
			"isBoolean" => 1
		));

		$this->addCheckBox("searchChildItemOnListPage", array(
			"selected" => $config->getSearchChildItemOnListPage(),
			"value" => 1,
			"name" => "Config[searchChildItemOnListPage]",
			"label" => "商品一覧ページで子商品のデータを取得する",
			"isBoolean" => 1
		));

		$this->addCheckBox("searchChildItemOnDetailPage", array(
			"selected" => $config->getSearchChildItemOnDetailPage(),
			"value" => 1,
			"name" => "Config[searchChildItemOnDetailPage]",
			"label" => "商品詳細ページで子商品のデータを取得する",
			"isBoolean" => 1
		));

		//子商品の詳細表示モード
		$this->addCheckBox("displayChildItem", array(
			"selected" => $config->getDisplayChildItem(),
			"value" => 1,
			"name" => "Config[displayChildItem]",
			"label" => "親商品の詳細ページにリダイレクトを行わないようにする",
			"isBoolean" => 1
		));

		//子商品の在庫管理モード
		$this->addCheckBox("childItemStock", array(
			"selected" => $config->getChildItemStock(),
			"value" => 1,
			"name" => "Config[childItemStock]",
			"label" => "子商品購入時に親商品の在庫数を減らす",
			"isBoolean" => 1
		));

		//子商品の在庫管理モード2
		$this->addCheckBox("noChildItemStock", array(
			"selected" => $config->getNoChildItemStock(),
			"value" => 1,
			"name" => "Config[noChildItemStock]",
			"label" => "子商品購入時に在庫数を減らさない",
			"isBoolean" => 1
		));

		//キャンセル注文含むモード
		$this->addCheckBox("displayCancelOrder", array(
			"selected" => $config->getDisplayCancelOrder(),
			"value" => 1,
			"name" => "Config[displayCancelOrder]",
			"label" => "注文一覧でキャンセルの注文も含める",
			"isBoolean" => 1
		));

		//仮登録確認モード
		$this->addCheckBox("checkPreOrder", array(
			"selected" => $config->getCheckPreOrder(),
			"value" => 1,
			"name" => "Config[checkPreOrder]",
			"label" => "注文検索の注文状況に仮登録(注文エラー)を表示する",
			"isBoolean" => 1
		));

		//注文キャンセル時に注文番号を壊す
		$this->addCheckBox("destroyTrackingNumberOnCancelOrder", array(
			"selected" => $config->getDestroyTrackingNumberOnCancelOrder(),
			"value" => 1,
			"name" => "Config[destroyTrackingNumberOnCancelOrder]",
			"label" => "注文キャンセル時に注文番号を壊す",
			"isBoolean" => 1
		));

		//注文状態の複数選択モード
		$this->addCheckBox("multiSelectStatusMode", array(
			"selected" => $config->getMultiSelectStatusMode(),
			"value" => 1,
			"name" => "Config[multiSelectStatusMode]",
			"label" => "注文検索で注文状態や支払い状況の複数項目の選択モードに切り替える",
			"isBoolean" => 1
		));

		$this->addInput("orderCSVExportLimit", array(
			"name" => "Config[orderCSVExportLimit]",
			"value" => (is_numeric($config->getOrderCSVExportLimit())) ? (int)$config->getOrderCSVExportLimit() : 1000
		));

		//購入手続きの進捗の有効期間
		$this->addInput("cartPageTimeLimit", array(
			"name" => "Config[cartPageTimeLimit]",
			"value" => $config->getCartPageTimeLimit()
		));

		$this->addInput("cartTryCountAndBanByIpAddress", array(
			"name" => "Config[cartTryCountAndBanByIpAddress]",
			"value" => $config->getCartTryCountAndBanByIpAddress()
		));

		$this->addInput("cartBanPeriod", array(
			"name" => "Config[cartBanPeriod]",
			"value" => $config->getCartBanPeriod()
		));

		//ログアウト後に表示するページ
		$this->addCheckBox("displayPageAfterLogout", array(
			"name" => "Config[displayPageAfterLogout]",
			"value" => 1,
			"selected" => $config->getDisplayPageAfterLogout(),
			"label" => "ログアウト後はログアウト前に開いていたページを表示する"
		));

		//お届け先情報の表示
		$this->addCheckBox("displaySendInformationForm", array(
			"name" => "Config[displaySendInformationForm]",
			"value" => 1,
			"selected" => $config->getDisplaySendInformationForm(),
			"label" => "カートでお届け先情報の入力欄を表示する"
		));

		//テンプレート編集画面に使用できるタグ一覧の表示
		$this->addCheckBox("displayUsableTagList", array(
			"name" => "Config[displayUsableTagList]",
			"value" => 1,
			"selected" => $config->getDisplayUsableTagList(),
			"label" => "テンプレートの編集画面で使用できるタグを表示"
		));

		$this->addCheckBox("useUserCode", array(
			"name" => "Config[useUserCode]",
			"value" => 1,
			"selected" => $config->getUseUserCode(),
			"label" => SHOP_USER_LABEL . "コードを使用する"
		));

		$this->addCheckBox("insertDummyMailAddress", array(
			"name" => "Config[insertDummyMailAddress]",
			"value" => 1,
			"selected" => $config->getInsertDummyMailAddress(),
			"label" => "管理画面にログイン時、公開側のカートのメールアドレスにダミーのメールアドレスを挿入する"
		));

		$this->addCheckBox("insertDummyMailAddressOnAdmin", array(
			"name" => "Config[insertDummyMailAddressOnAdmin]",
			"value" => 1,
			"selected" => $config->getInsertDummyMailAddressOnAdmin(),
			"label" => "管理画面からの注文時、" . SHOP_USER_LABEL . "のメールアドレスにダミーのメールアドレスを挿入する"
		));

		$this->addCheckBox("insertDummyMailAddressOnAdminRegister", array(
			"name" => "Config[insertDummyMailAddressOnAdminRegister]",
			"value" => 1,
			"selected" => $config->getInsertDummyMailAddressOnAdminRegister(),
			"label" => "管理画面からの" . SHOP_USER_LABEL . "登録時、メールアドレスにダミーのメールアドレスを挿入する"
		));

		$this->addCheckBox("insertDummyAddressOnAdmin", array(
			"name" => "Config[insertDummyAddressOnAdmin]",
			"value" => 1,
			"selected" => $config->getInsertDummyAddressOnAdmin(),
			"label" => "管理画面からの注文時、" . SHOP_USER_LABEL . "の住所にダミーの値を挿入できるボタンを表示する"
		));

		// $this->addCheckBox("isChildItemOnAdminOrder", array(
		// 	"name" => "Config[isChildItemOnAdminOrder]",
		// 	"value" => 1,
		// 	"selected" => $config->getIsChildItemOnAdminOrder(),
		// 	"label" => "管理画面からの注文の際に子商品を検索結果に含める"
		// ));

		$this->addCheckBox("isUnregisteredItem", array(
			"name" => "Config[isUnregisteredItem]",
			"value" => 1,
			"selected" => $config->getIsUnregisteredItem(),
			"label" => "管理画面からの注文の際に未登録商品の追加を許可する"
		));

		$this->addCheckBox("displayRegisterAfterItemSearchOnAdmin", array(
			"name" => "Config[displayRegisterAfterItemSearchOnAdmin]",
			"value" => 1,
			"selected" => $config->getDisplayRegisterAfterItemSearchOnAdmin(),
			"label" => "管理画面からの注文の際に商品検索後に商品を登録するフォームを表示する"
		));

		$this->addCheckBox("addSearchChildItemNameOnAdmin", array(
			"name" => "Config[addSearchChildItemNameOnAdmin]",
			"value" => 1,
			"selected" => $config->getAddSearchChildItemNameOnAdmin(),
			"label" => "管理画面からの注文の際に商品検索で子商品を加味して検索をする"
		));

		$this->addCheckBox("allowRegistrationZeroYenProducts", array(
			"name" => "Config[allowRegistrationZeroYenProducts]",
			"value" => 1,
			"selected" => $config->getAllowRegistrationZeroYenProducts(),
			"label" => "管理画面からの注文の際に0円の商品(未登録商品のみ)をカートに入れる事を許可する"
		));

		$this->addCheckBox("allowRegistrationZeroQuantityProducts", array(
			"name" => "Config[allowRegistrationZeroQuantityProducts]",
			"value" => 1,
			"selected" => $config->getAllowRegistrationZeroQuantityProducts(),
			"label" => "管理画面からの注文の際に商品(未登録商品のみ)をカートに0個入れる事を許可する"
		));

		$this->addCheckBox("changeParentItemNameOnAdmin", array(
			"name" => "Config[changeParentItemNameOnAdmin]",
			"value" => 1,
			"selected" => $config->getChangeParentItemNameOnAdmin(),
			"label" => "管理画面でカートや注文詳細で表記されている子商品名を親商品名に変換する"
		));

		$this->addCheckBox("displayPurchasePriceOnAdmin", array(
			"name" => "Config[displayPurchasePriceOnAdmin]",
			"value" => 1,
			"selected" => $config->getDisplayPurchasePriceOnAdmin(),
			"label" => "管理画面からの注文の際に単価の横に仕入値を表示する"
		));

		$this->addCheckBox("displayOrderAdminPage", array(
			"name" => "Config[displayOrderAdminPage]",
			"value" => 1,
			"selected" => $config->getDisplayOrderAdminPage(),
			"label" => "管理画面で注文タブを表示する"
		));

		$this->addCheckBox("displayItemAdminPage", array(
			"name" => "Config[displayItemAdminPage]",
			"value" => 1,
			"selected" => $config->getDisplayItemAdminPage(),
			"label" => "管理画面で商品タブを表示する"
		));

		$this->addCheckBox("displayUserAdminPage", array(
			"name" => "Config[displayUserAdminPage]",
			"value" => 1,
			"selected" => $config->getDisplayUserAdminPage(),
			"label" => "管理画面で" . SHOP_USER_LABEL . "タブを表示する"
		));

		$this->addCheckBox("displayOrderButtonOnUserAdminPage", array(
			"name" => "Config[displayOrderButtonOnUserAdminPage]",
			"value" => 1,
			"selected" => $config->getDisplayOrderButtonOnUserAdminPage(),
			"label" => "管理画面の" . SHOP_USER_LABEL . "詳細で注文関連のボタンを表示する"
		));

		$this->addInput("autoOperateAuthorId", array(
			"name" => "Config[autoOperateAuthorId]",
			"value" => $config->getAutoOperateAuthorId(),
			"attr:placeholder" => "soyshop",
			"attr:required" => "required"
		));

		SOY2::import("domain.config.SOYShop_Area");
		$this->addSelect("defaultArea", array(
			"name" => "Config[defaultArea]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => $config->getDefaultArea()
		));

		$this->addCheckBox("displayUserOfficeItems", array(
			"name" => "Config[displayUserOfficeItems]",
			"value" => 1,
			"selected" => $config->getDisplayUserOfficeItems(),
			"label" => "勤務先関連の項目を表示する(" . SHOP_USER_LABEL . "詳細他、注文時のお届け先等)"
		));

		$this->addCheckBox("displayUserProfileItems", array(
			"name" => "Config[displayUserProfileItems]",
			"value" => 1,
			"selected" => $config->getDisplayUserProfileItems(),
			"label" => SHOP_USER_LABEL . "詳細の編集画面でプロフィール関連の項目を表示する"
		));

		$this->addCheckBox("insertDummyItemCode", array(
			"name" => "Config[insertDummyItemCode]",
			"value" => 1,
			"selected" => $config->getInsertDummyItemCode(),
			"label" => "管理画面からの商品登録時、商品コードにダミーのコードを挿入する"
		));

		$this->addInput("dummyItemCodeRule", array(
			"name" => "Config[dummyItemCodeRule]",
			"value" => $config->getDummyItemCodeRule(),
			"attr:placeholder" => "空欄でランダム"
		));

		$this->addCheckBox("displayItemKeywords", array(
			"name" => "Config[displayItemKeywords]",
			"value" => 1,
			"selected" => $config->getDisplayItemKeywords(),
			"label" => "商品情報の登録画面でキーワードの入力画面を表示する"
		));

		$this->addCheckBox("displayItemDescription", array(
			"name" => "Config[displayItemDescription]",
			"value" => 1,
			"selected" => $config->getDisplayItemDescription(),
			"label" => "商品情報の登録画面で説明の入力画面を表示する"
		));

		$this->addCheckBox("displayItemImage", array(
			"name" => "Config[displayItemImage]",
			"value" => 1,
			"selected" => $config->getDisplayItemImage(),
			"label" => "商品情報の登録画面で商品画像の登録を表示する"
		));

		//メンテナンスモード
		$this->addCheckBox("isShowOnlyAdministrator", array(
			"selected" => $config->getIsShowOnlyAdministrator(),
			"value" => 1,
			"name" => "Config[isShowOnlyAdministrator]",
			"label" => "カートをメンテナンスモードに切り替える(カートの停止)",
			"isBoolean" => 1
		));

		//マルチカテゴリモード
		$this->addCheckBox("multiCategory", array(
			"selected" => $config->getMultiCategory(),
			"value" => 1,
			"name" => "Config[multiCategory]",
			"label" => "一つの商品で複数のカテゴリ設定を許可する",
			"isBoolean" => 1
		));
		$this->addModel("multiCategory_batch", array(
			"visible" => ($this->checkMultiCategory())
		));
		$this->addLink("batch_url", array(
			"link" => SOY2PageController::createLink("") . "?upgrade=1.5.1",
			"text" => "アップデートバッチファイルを実行してください"
		));

		/*** 顧客情報入力フォームの設定 ***/
		$this->createAdd("form_config_list", "_common.Config.CustomerFormListComponent", array(
			"list" => $config->getCustomerDisplayFormConfigList(),
			"formConfig" => $config->getCustomerDisplayFormConfig(),
			"customerConfig" => $config->getCustomerInformationConfig(),
			"adminConfig" => $config->getCustomerAdminConfig()
		));

		$this->addInput("require_text", array(
			"name" => "Config[requireText]",
			"value" => $config->getRequireText()
		));

		/** 管理画面の注文一覧の設定 **/
		$this->createAdd("order_item_list", "_common.Config.OrderItemListComponent", array(
			"list" => $config->getOrderItemList(),
			"orderConfig" => $config->getOrderItemConfig()
		));
	}

	private function getTaxModuleList(){
		try{
			$plugins = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByType("tax");
		}catch(Exception $e){
			return array();
		}

		$list = array();

		foreach($plugins as $plugin){
			if(is_null($plugin->getName()) || is_null($plugin->getPluginId())) continue;
			if($plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) continue;
			$list[$plugin->getPluginId()] = $plugin->getName();
		}

		return $list;
	}

	private function buildTaxModuleLink($pluginId){
		$plugin = soyshop_get_plugin_object($pluginId);
		if(is_null($plugin->getId()) || $plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) return "";

		return SOY2PageController::createLink("Config.Detail?plugin=" . $pluginId);
	}

	/**
	 * @boolean
	 */
	function checkMultiCategory(){
		$db = new SOY2DAO();
		$exist = "select * from soyshop_categories;";

		try{
			$res = $db->executeQuery($exist);
		}catch(Exception $e){
			return true;
		}

		//テーブルが取得できた場合はfalseを返す
		return (is_array($res)) ? false : true;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("基本設定", array("Config" => "設定"));
	}
}


class RadioButtonList extends HTMlList{

	private $name;
	private $selected;

	protected function populateItem($item, $key) {

		$this->addCheckBox("button", array(
			"value" => $key,
			"name" => $this->name,
			"selected" => ($key == $this->selected)
		));
	}

	/**#@+
	 *
	 * @access public
	 */
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	/**#@-*/

	/**#@+
	 *
	 * @access public
	 */
	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
	/**#@-*/
}
