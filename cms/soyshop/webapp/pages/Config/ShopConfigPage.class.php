<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class ShopConfigPage extends WebPage{

	private $config;
	private $pluginDao;

	function doPost(){
		$config = $this->config;

		foreach(array(
			"consumptionTaxInclusivePricingRate" => SOYShop_ShopConfig::CONSUMPTION_TAX_RATE,
			"consumptionTaxInclusiveCommission" => 0,
			"displayStockCount" => 0,
			"displayPageAfterLogout" => 0,
			"displaySendInformationForm" => 0,
			"allowMailAddressLogin" => 0,
			"allowLoginIdLogin" => 0,
			"displayUsableTagList" => 0,
			"insertDummyMailAddress" => 0,
			"insertDummyMailAddressOnAdmin" => 0,
			"insertDummyMailAddressOnAdminRegister" => 0,
			"displayOrderAdminPage" => 0,
			"displayItemAdminPage" => 0,
			"displayOrderButtonOnUserAdminPage" => 0,
			"defalutArea" => 0,
			"displayUserOfficeItems" => 0,
			"displayUserProfileItems" => 0,
		) as $key => $null){
			$_POST["Config"][$key] = (isset($_POST["Config"][$key])) ? (int)$_POST["Config"][$key] : $null;
		}

		if(!isset($_POST["Config"]["consumptionTaxModule"])) $_POST["Config"]["consumptionTaxModule"] = null;

		$consumptionTax = $_POST["Config"]["consumptionTax"]; //外税
		$taxInclusive = $_POST["Config"]["consumptionTaxInclusivePricing"]; //内税

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
		$this->pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

		parent::__construct();

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

		foreach($company as $key => $value){
			$this->addInput("company_" . $key, array(
				"name" => "Config[companyInformation][$key]",
				"value" => $value
			));
		}

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

		//在庫無視モード
		$this->addCheckBox("ignoreStock", array(
			"selected" => $config->getIgnoreStock(),
			"value" => 1,
			"name" => "Config[ignoreStock]",
			"label" => "無視する",
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

		//仮登録確認モード
		$this->addCheckBox("checkPreOrder", array(
			"selected" => $config->getCheckPreOrder(),
			"value" => 1,
			"name" => "Config[checkPreOrder]",
			"label" => "注文検索の注文状況に仮登録(注文エラー)を表示する",
			"isBoolean" => 1
		));

		//購入手続きの進捗の有効期間
		$this->addInput("cartPageTimeLimit", array(
			"name" => "Config[cartPageTimeLimit]",
			"value" => $config->getCartPageTimeLimit()
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
			"label" => "管理画面からの注文時、顧客のメールアドレスにダミーのメールアドレスを挿入する"
		));

		$this->addCheckBox("insertDummyMailAddressOnAdminRegister", array(
			"name" => "Config[insertDummyMailAddressOnAdminRegister]",
			"value" => 1,
			"selected" => $config->getInsertDummyMailAddressOnAdminRegister(),
			"label" => "管理画面からの顧客登録時、メールアドレスにダミーのメールアドレスを挿入する"
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

		$this->addCheckBox("displayOrderButtonOnUserAdminPage", array(
			"name" => "Config[displayOrderButtonOnUserAdminPage]",
			"value" => 1,
			"selected" => $config->getDisplayOrderButtonOnUserAdminPage(),
			"label" => "管理画面の顧客詳細で注文関連のボタンを表示する"
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
			"label" => "顧客詳細の編集画面で勤務先関連の項目を表示する"
		));

		$this->addCheckBox("displayUserProfileItems", array(
			"name" => "Config[displayUserProfileItems]",
			"value" => 1,
			"selected" => $config->getDisplayUserProfileItems(),
			"label" => "顧客詳細の編集画面でプロフィール関連の項目を表示する"
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
			"customerConfig" => $config->getCustomerInformationConfig()
		));

		/** 管理画面の注文一覧の設定 **/
		$this->createAdd("order_item_list", "_common.Config.OrderItemListComponent", array(
			"list" => $config->getOrderItemList(),
			"orderConfig" => $config->getOrderItemConfig()
		));
	}

	private function getTaxModuleList(){

		try{
			$plugins = $this->pluginDao->getByType("tax");
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
		try{
			$plugin = $this->pluginDao->getByPluginId($pluginId);
		}catch(Exception $e){
			return "";
		}

		if($plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) return "";

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
?>
