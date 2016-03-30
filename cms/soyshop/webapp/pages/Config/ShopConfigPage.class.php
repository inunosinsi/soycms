<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class ShopConfigPage extends WebPage{

	private $config;
	private $pluginDao;

	function doPost(){
		$config = $this->config;

		$_POST["Config"]["consumptionTaxInclusivePricingRate"] = (isset($_POST["Config"]["consumptionTaxInclusivePricingRate"])) ? (int)$_POST["Config"]["consumptionTaxInclusivePricingRate"] : SOYShop_ShopConfig::CONSUMPTION_TAX_RATE;
		$_POST["Config"]["displayStockCount"] = (isset($_POST["Config"]["displayStockCount"])) ? (int)$_POST["Config"]["displayStockCount"] : 0;
		$_POST["Config"]["displayPageAfterLogout"] = (isset($_POST["Config"]["displayPageAfterLogout"])) ? (int)$_POST["Config"]["displayPageAfterLogout"] : 0;
		$_POST["Config"]["displaySendInformationForm"] = (isset($_POST["Config"]["displaySendInformationForm"])) ? (int)$_POST["Config"]["displaySendInformationForm"] : 0;
		$_POST["Config"]["allowMailAddressLogin"] = (isset($_POST["Config"]["allowMailAddressLogin"])) ? (int)$_POST["Config"]["allowMailAddressLogin"] : 0;
		$_POST["Config"]["allowLoginIdLogin"] = (isset($_POST["Config"]["allowLoginIdLogin"])) ? (int)$_POST["Config"]["allowLoginIdLogin"] : 0;
		$_POST["Config"]["displayUsableTagList"] = (isset($_POST["Config"]["displayUsableTagList"])) ? (int)$_POST["Config"]["displayUsableTagList"] : 0;
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

	function ShopConfigPage() {
		$this->config = SOYShop_ShopConfig::load();
		$this->pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

		WebPage::WebPage();

		error_reporting(E_ALL ^ E_NOTICE);
		$this->buildForm();

		$this->addLink("return_link", array(
			"link" => SOY2PageController::createLink("Config")
		));
	}

	function buildForm(){
		$config = $this->config;

		$this->addModel("is_updated", array(
			"visible" => (isset($_GET["updated"]))
		));

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
		$this->addModel("consumptionTaxInclusivePricingModeOff", array(
			"visible" => (!$config->getConsumptionTaxInclusivePricing())
		));

		$this->addCheckBox("consumptionTax", array(
			"selected" => $config->getConsumptionTax(),
			"value" => 1,
			"name" => "Config[consumptionTax]",
			"label" => "税別価格(外税)を表示する",
			"isBoolean" => 1
		));

		$taxModuleList = self::getTaxModuleList();
		$this->addModel("noTaxModuleList", array(
			"visible" => (count($taxModuleList) === 0)
		));
		$this->addModel("isTaxModuleList", array(
			"visible" => (count($taxModuleList) > 0)
		));
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

		$this->addModel("consumptionTaxModeOff", array(
			"visible" => (!$config->getConsumptionTax())
		));

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
		$formConfig = $config->getCustomerDisplayFormConfig();
		$customer = $config->getCustomerInformationConfig();
		
		$this->addCheckBox("mailAddress_form", array(
			"selected" => true,
			"value" => 1,
			"onclick" => "return false",
			"name" => "Config[CustomerDisplayFormConfig][mailAddress]"
		));

		$this->addCheckBox("mailAddress", array(
			"selected" => true,
			"value" => 1,
			"onclick" => "return false",
			"name" => "Config[CustomerInformationConfig][mailAddress]"
		));
		
		$this->addCheckBox("accountId_form", array(
			"selected" => $formConfig["accountId"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][accountId]"
		));

		$this->addCheckBox("accountId", array(
			"selected" => $customer["accountId"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][accountId]"
		));
		
		$this->addCheckBox("name_form", array(
			"selected" => true,
			"value" => 1,
			"onclick" => "return false",
			"name" => "Config[CustomerDisplayFormConfig][name]"
		));

		$this->addCheckBox("name", array(
			"selected" => true,
			"value" => 1,
			"onclick" => "return false",
			"name" => "Config[CustomerInformationConfig][name]"
		));
		
		$this->addCheckBox("reading_form", array(
			"selected" => $formConfig["reading"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][reading]"
		));

		$this->addCheckBox("reading", array(
			"selected" => $customer["reading"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][reading]"
		));
		
		$this->addCheckBox("nickname_form", array(
			"selected" => $formConfig["nickname"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][nickname]"
		));

		$this->addCheckBox("nickname", array(
			"selected" => $customer["nickname"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][nickname]"
		));
		
		$this->addCheckBox("zipCode_form", array(
			"selected" => $formConfig["zipCode"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][zipCode]"
		));

		$this->addCheckBox("zipCode", array(
			"selected" => $customer["zipCode"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][zipCode]"
		));
		
		$this->addCheckBox("address_form", array(
			"selected" => $formConfig["address"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][address]"
		));

		$this->addCheckBox("address", array(
			"selected" => $customer["address"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][address]"
		));
		
		$this->addCheckBox("telephoneNumber_form", array(
			"selected" => $formConfig["telephoneNumber"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][telephoneNumber]"
		));

		$this->addCheckBox("telephoneNumber", array(
			"selected" => $customer["telephoneNumber"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][telephoneNumber]"
		));
		
		$this->addCheckBox("gender_form", array(
			"selected" => $formConfig["gender"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][gender]"
		));

		$this->addCheckBox("gender", array(
			"selected" => $customer["gender"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][gender]"
		));
		
		$this->addCheckBox("birthday_form", array(
			"selected" => $formConfig["birthday"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][birthday]"
		));

		$this->addCheckBox("birthday", array(
			"selected" => $customer["birthday"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][birthday]"
		));
		
		$this->addCheckBox("faxNumber_form", array(
			"selected" => $formConfig["faxNumber"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][faxNumber]"
		));

		$this->addCheckBox("faxNumber", array(
			"selected" => $customer["faxNumber"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][faxNumber]"
		));
		
		$this->addCheckBox("cellphoneNumber_form", array(
			"selected" => $formConfig["cellphoneNumber"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][cellphoneNumber]"
		));

		$this->addCheckBox("cellphoneNumber", array(
			"selected" => $customer["cellphoneNumber"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][cellphoneNumber]"
		));
		
		$this->addCheckBox("url_form", array(
			"selected" => $formConfig["url"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][url]"
		));

		$this->addCheckBox("url", array(
			"selected" => $customer["url"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][url]"
		));
		
		$this->addCheckBox("jobName_form", array(
			"selected" => $formConfig["jobName"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][jobName]"
		));

		$this->addCheckBox("jobName", array(
			"selected" => $customer["jobName"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][jobName]"
		));
		
		$this->addCheckBox("memo_form", array(
			"selected" => $formConfig["memo"],
			"value" => 1,
			"name" => "Config[CustomerDisplayFormConfig][memo]"
		));

		$this->addCheckBox("memo", array(
			"selected" => $customer["memo"],
			"value" => 1,
			"name" => "Config[CustomerInformationConfig][memo]"
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