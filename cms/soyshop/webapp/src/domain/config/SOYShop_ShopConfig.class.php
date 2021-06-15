<?php

class SOYShop_ShopConfig {

	const CONSUMPTION_TAX_MODE_ON = 1;
	const CONSUMPTION_TAX_RATE = 5;

	const SSL_CONFIG_HTTP = "http";		//常にhttp
	const SSL_CONFIG_HTTPS = "https";		//常にhttps(SSL)
	const SSL_CONFIG_LOGIN = "login";		//ログインしている時のみ

	private $shopName;
	private $siteUrl;

	private $adminUrl;

	private $appName = "SOY Shop";
	private $appLogoPath = "./img/logo.png";
	private $isTrailingSlash = 1;	//カノニカルURLでトライリングスラッシュの有無
	private $isDomainWww = 1;		//www.ありドメインの時のカノニカルURLの出力設定

	private $consumptionTax = 0;
	private $consumptionTaxModule;
	private $consumptionTaxInclusivePricing = 0;
	private $consumptionTaxInclusivePricingRate;
	private $consumptionTaxInclusiveCommission = 1;
	private $sslConfig = "http";
	private $isOrderListOneYearsWonth = 1;	//注文一覧の標準表示件数を一年分にする
	private $displayStock;
	private $displayStockCount = 0;
	private $ignoreStock;
	private $isHiddenStockCount;	//在庫数無視モードで在庫数を隠すか？
	private $searchChildItemOnListPage = 1;
	private $searchChildItemOnDetailPage = 1;
	private $displayChildItem;
	private $childItemStock;
	private $noChildItemStock;
	private $displayCancelOrder;
	private $checkPreOrder;
	private $destroyTrackingNumberOnCancelOrder = 0;
	private $multiSelectStatusMode;
	private $orderCSVExportLimit = 1000;
	private $isShowOnlyAdministrator;
	private $multiCategory;
	private $cartPageTimeLimit = 30;//デフォルトは30分
	private $cartTryCountAndBanByIpAddress = 10;	//カートの使用禁止までのトライ回数	デフォルトは10回
	private $cartBanPeriod = 3;			//使用禁止にしたカートを再び使用できるようになるまでの期間　デフォルトは3時間
	private $displayPageAfterLogout = 0;
	private $displaySendInformationForm = 1;
	private $allowMailAddressLogin = 1;
	private $allowLoginIdLogin = 0;
	private $accountIdItemName = "ログインID";
	private $passwordCount = 8;		//パスワードの最低文字数
	private $displayUsableTagList = 0;
	private $useUserCode = 0;			//顧客コード
	private $insertDummyMailAddress = 1;
	private $insertDummyMailAddressOnAdmin = 0;
	private $insertDummyMailAddressOnAdminRegister = 0;
	private $insertDummyAddressOnAdmin = 0;

	//private $isChildItemOnAdminOrder = 0;	//管理画面からの注文の際に子商品を検索結果に含める
	private $isUnregisteredItem = 1;		//管理画面からの注文の際に未登録商品の追加を許可する
	private $displayRegisterAfterItemSearchOnAdmin = 1;	//管理画面からの注文の際に商品検索後に商品を登録するフォームを表示する
	private $addSearchChildItemNameOnAdmin = 1;			//管理画面からの注文の際に商品検索で子商品を加味して検索をする
	private $allowRegistrationZeroYenProducts = 0;	//管理画面からの注文の際に0円の商品をカートに入れる事を許可する
	private $allowRegistrationZeroQuantityProducts = 0;	//管理画面からの注文の際に商品をカートに0個入れる事を許可する
	private $changeParentItemNameOnAdmin = 0;	//管理画面でカートや注文詳細で表記されている子商品名を親商品名に変換する
	private $displayPurchasePriceOnAdmin = 0;	//管理画面からの注文の際に単価の横に仕入値を出力する

	private $displayOrderAdminPage = 1;
	private $displayItemAdminPage = 1;
	private $displayUserAdminPage = 1;
	private $displayOrderButtonOnUserAdminPage = 1;

	private $autoOperateAuthorId = "soyshop";	//注文等の操作者が不在の時の対応者ID

	private $defaultArea = 0;
	private $displayUserOfficeItems = 1;
	private $displayUserProfileItems = 1;

	private $insertDummyItemCode = 0;
	private $dummyItemCodeRule;
	private $displayItemKeywords = 1;
	private $displayItemDescription = 1;
	private $displayItemImage = 1;

	private $companyInformation = array(
		"name" => "",
		"address1" => "",
		"address2" => "",
		"building" => "",	//建物名
		"telephone" => "",
		"fax" => "",
		"mailaddress" => "",
	);

	// 顧客情報入力フォームの設定
	private $customerDisplayFormConfig = array();
	public static $customerDisplayConfigDefault = array(
		"mailAddress"	=>	true,
		"accountId"		=>	false,
		"name"			=>	true,
		"reading"		=>	true,
		"honorific"		=>  false,
		"nickname" 		=>	true,
		"zipCode"		=>	true,
		"address"		=>	true,
		"telephoneNumber"	=> true,
		"gender"		=> true,
		"birthday"		=> true,
		"faxNumber"		=> true,
		"cellphoneNumber"	=> true,
		"url"			=> true,
		"jobName"		=> true,
		"memo"			=> true
	);

	private $customerInformationConfig = array();
	public static $customerConfigDefault = array(
		"mailAddress"	=>	true,
		"accountId" 	=>	false,
		"name"			=>	true,
		"reading"		=>	true,
		"honorific"		=> false,
		"nickname"		=>	false,
		"zipCode"		=>	true,
		"address"		=>	true,
		"telephoneNumber"	=> true,
		"gender"		=> false,
		"birthday"		=> false,
		"faxNumber"		=> false,
		"cellphoneNumber"	=> false,
		"url"			=> false,
		"jobName"		=> false,
		"memo"			=> false
	);

	private $customerAdminConfig = array();
	public static $customerAdminConfigDefault = array(
		"mailAddress"	=>	true,
		"accountId" 	=>	true,
		"name"			=>	true,
		"reading"		=>	true,
		"honorific"		=> 	false,
		"nickname"		=>	true,
		"zipCode"		=>	true,
		"address"		=>	true,
		"telephoneNumber"	=> true,
		"gender"		=> true,
		"birthday"		=> true,
		"faxNumber"		=> true,
		"cellphoneNumber"	=> true,
		"url"			=> true,
		"jobName"		=> true,
		"memo"			=> true
	);

	function getCustomerDisplayFormConfigList(){
		return array(
			"mailAddress"	=>	"メールアドレス",
			"accountId"		=>	"ログインID(マイページのみ)",
			"name"			=>	"名前",
			"reading"		=>	"フリガナ",
			"honorific"		=>	"敬称(管理画面のみ)",
			"nickname" 		=>	"ニックネーム(マイページのみ)",
			"zipCode"		=>	"郵便番号",
			"address"		=>	"住所",
			"telephoneNumber"	=> "電話番号",
			"gender"		=> "性別",
			"birthday"		=> "生年月日",
			"faxNumber"		=> "FAX番号",
			"cellphoneNumber"	=> "携帯番号",
			"url"			=> "URL(マイページのみ)",
			"jobName"		=> "職業",
			"memo"			=> "備考"
		);;
	}

	private $requireText = "(必須)";

	private $orderItemConfig = array();
	public static $orderItemConfigDefault = array(
		"orderId" => true,
		"trackingNumber" => true,
		"orderDate" => true,
		"customerName" => true,
		"totalPrice" => true,
		"status" => true,
		"paymentStatus" => true,
		"confirmMail" => false,
		"paymentMail" => true,
		"deliveryMail" => true
	);

	function getOrderItemList(){
		return array(
			"orderId" => "注文ID",
			"trackingNumber" => "注文番号",
			"orderDate" => "注文時刻",
			"customerName" => "顧客名",
			"totalPrice" => "合計金額",
			"status" => "状態",
			"paymentStatus" => "支払い状態",
			"confirmMail" => "注文確認メール",
			"paymentMail" => "支払確認メール",
			"deliveryMail" => "発送メール"
		);
	}


	const DATASETS_KEY = "soyshop.ShopConfig";

	public static function load(){
		return SOYShop_DataSets::get(self::DATASETS_KEY,new SOYShop_ShopConfig());
	}

	//siteUrlはcms側の各dbに入れるURL
	public static function save(SOYShop_ShopConfig $obj, $siteUrl = null){
		$obj->setAdminUrl(SOY2PageController::createRelativeLink(SOYSHOP_ADMIN_URL, true));
		SOYShop_DataSets::put(self::DATASETS_KEY, $obj);

		if(!class_exists("SOYAppUtil")) SOY2::import("util.SOYAppUtil");

		/**
		 * shop.db site_nameとurlの変更→廃止
		 */
		//self::saveShopDbSiteConfig($obj->getShopName(), $siteUrl);

		/**
		 * cms.db site_nameとurlの変更
		 */
		self::saveCmsDbSiteConfig($obj->getShopName(), $siteUrl);
	}

	public static function getSSLConfigList(){
		return array(
			self::SSL_CONFIG_HTTP => "常にhttpで表示する",
			self::SSL_CONFIG_HTTPS => "常にhttpsで表示する",
			self::SSL_CONFIG_LOGIN => "ログイン時のみhttpsで表示する",
		);
	}

	//廃止
	// private static function saveShopDbSiteConfig($shopName, $publishUrl){
	// 	$old = SOYAppUtil::switchAppMode("shop");
	// 	$shopSiteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
	// 	try{
	// 		$site = $shopSiteDao->getBySiteId(SOYSHOP_ID);
	// 	}catch(Exception $e){
	// 		$site = new SOYShop_Site();
	// 	}
	//
	// 	if(!is_null($site->getId())){
	// 		$site->setName($shopName);
	//
	// 		if(isset($publishUrl) && strlen($publishUrl)){
	// 			$site->setUrl($publishUrl);
	// 		}
	//
	// 		try{
	// 			$shopSiteDao->update($site);
	// 		}catch(Exception $e){
	// 			//
	// 		}
	// 	}
	// 	SOYAppUtil::resetAppMode($old);
	// }

	private static function saveCmsDbSiteConfig($shopName, $publishUrl){
		$old = SOYAppUtil::switchAdminDsn();
		$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$site = $siteDao->getBySiteId(SOYSHOP_ID);
		}catch(Exception $e){
			$site = new Site();
		}


		if(!is_null($site->getId())){
			$site->setSiteName($shopName);

			if(isset($publishUrl) && strlen($publishUrl)){
				$site->setUrl($publishUrl);
			}

			try{
				$siteDao->update($site);
			}catch(Exception $e){
				//
			}
		}

		SOYAppUtil::resetAdminDsn($old);
	}

	function getCustomerDisplayFormConfig(){
		if(count($this->customerDisplayFormConfig)){
			return $this->customerDisplayFormConfig;
		}else{
			return SOYShop_ShopConfig::$customerDisplayConfigDefault;
		}
	}

	function setCustomerDisplayFormConfig($array){
		$this->customerDisplayFormConfig = SOYShop_ShopConfig::$customerDisplayConfigDefault;

		foreach($this->customerDisplayFormConfig as $key => $value){
			$this->customerDisplayFormConfig[$key] = (boolean)@$array[$key];
		}

		//mailAddres
		//は必須
		$this->customerDisplayFormConfig["mailAddress"] = true;
		//$this->customerDisplayFormConfig["name"] = true;
	}

	function getCustomerInformationConfig() {
		if(count($this->customerInformationConfig)){
			return $this->customerInformationConfig;
		}else{
			return SOYShop_ShopConfig::$customerConfigDefault;
		}

	}
	function setCustomerInformationConfig($array) {

		$this->customerInformationConfig = SOYShop_ShopConfig::$customerConfigDefault;

		foreach($this->customerInformationConfig as $key => $value){
			$this->customerInformationConfig[$key] = (boolean)@$array[$key];
		}

		//mailAddres
		//は必須
		$this->customerInformationConfig["mailAddress"] = true;
		//$this->customerInformationConfig["name"] = true;
	}

	function getCustomerAdminConfig() {
		if(count($this->customerAdminConfig)){
			return $this->customerAdminConfig;
		}else{
			return SOYShop_ShopConfig::$customerAdminConfigDefault;
		}

	}
	function setCustomerAdminConfig($array) {

		$this->customerAdminConfig = SOYShop_ShopConfig::$customerAdminConfigDefault;

		foreach($this->customerAdminConfig as $key => $value){
			$this->customerAdminConfig[$key] = (boolean)@$array[$key];
		}

		//mailAddres
		//name
		//は必須
		$this->customerAdminConfig["mailAddress"] = true;
		$this->customerAdminConfig["name"] = true;
	}

	function getOrderItemConfig(){
		if(count($this->orderItemConfig)){
			return $this->orderItemConfig;
		}else{
			return SOYShop_ShopConfig::$orderItemConfigDefault;
		}
	}

	function setOrderItemConfig($array){
		$this->orderItemConfig = SOYShop_ShopConfig::$orderItemConfigDefault;

		foreach($this->orderItemConfig as $key => $value){
			$this->orderItemConfig[$key] = (boolean)@$array[$key];
		}
	}

	function getShopName() {
		if(strlen($this->shopName) < 1) return "新しいショップ";
		return $this->shopName;
	}
	function setShopName($shopName) {
		$this->shopName = $shopName;
	}

	function getSiteUrl(){
		if(is_null($this->siteUrl)){
			$this->siteUrl = soyshop_get_site_url(true);
		}
		return $this->siteUrl;
	}

	function setSiteUrl($siteUrl){
		$this->siteUrl = $siteUrl;
	}

	function getCompanyInformation() {
		$array = array(
			"name" => "",
			"address1" => "",
			"address2" => "",
			"building" => "",
			"telephone" => "",
			"fax" => "",
			"mailaddress" => "",
		);

		$this->companyInformation = array_intersect_key($this->companyInformation, $array);
		$companyInformation = array_merge($array, $this->companyInformation);
		$this->companyInformation = $companyInformation;

		return $this->companyInformation;
	}
	function setCompanyInformation($companyInformation) {
		$this->companyInformation = $companyInformation;
	}

	function getAdminUrl() {
		return $this->adminUrl;
	}
	function setAdminUrl($adminUrl) {
		$this->adminUrl = $adminUrl;
	}

	function getAppName(){
		return $this->appName;
	}
	function setAppName($appName){
		$this->appName = $appName;
	}

	function getAppLogoPath(){
		return $this->appLogoPath;
	}
	function setAppLogoPath($appLogoPath){
		$this->appLogoPath = $appLogoPath;
	}

	function getIsTrailingSlash(){
		return $this->isTrailingSlash;
	}
	function setIsTrailingSlash($isTrailingSlash){
		$this->isTrailingSlash = $isTrailingSlash;
	}

	function getIsDomainWww(){
		return $this->isDomainWww;
	}
	function setIsDomainWww($isDomainWww){
		$this->isDomainWww = $isDomainWww;
	}

	function getConsumptionTax(){
		return $this->consumptionTax;
	}
	function setConsumptionTax($consumptionTax){
		$this->consumptionTax = $consumptionTax;
	}

	function getConsumptionTaxModule(){
		return $this->consumptionTaxModule;
	}
	function setConsumptionTaxModule($consumptionTaxModule){
		$this->consumptionTaxModule = $consumptionTaxModule;
	}

	function getConsumptionTaxInclusivePricing(){
		return $this->consumptionTaxInclusivePricing;
	}
	function setConsumptionTaxInclusivePricing($consumptionTaxInclusivePricing){
		$this->consumptionTaxInclusivePricing = $consumptionTaxInclusivePricing;
	}

	function getConsumptionTaxInclusivePricingRate(){
		$taxRate = $this->consumptionTaxInclusivePricingRate;
		if(is_null($taxRate)) $taxRate = self::CONSUMPTION_TAX_RATE;
		return $taxRate;
	}
	function setConsumptionTaxInclusivePricingRate($consumptionTaxInclusivePricingRate){
		$this->consumptionTaxInclusivePricingRate = $consumptionTaxInclusivePricingRate;
	}

	function getConsumptionTaxInclusiveCommission(){
		return $this->consumptionTaxInclusiveCommission;
	}

	function setConsumptionTaxInclusiveCommission($consumptionTaxInclusiveCommission){
		$this->consumptionTaxInclusiveCommission = $consumptionTaxInclusiveCommission;
	}

	function getSSLConfig(){
		return $this->sslConfig;
	}

	function setSSLConfig($sslConfig){
		$this->sslConfig = $sslConfig;
	}

	function getIsOrderListOneYearsWonth(){
		return $this->isOrderListOneYearsWonth;
	}
	function setIsOrderListOneYearsWonth($isOrderListOneYearsWonth){
		$this->isOrderListOneYearsWonth = $isOrderListOneYearsWonth;
	}

	function getDisplayStock() {
		return $this->displayStock;
	}
	function setDisplayStock($displayStock) {
		$this->displayStock = $displayStock;
	}

	function getDisplayStockCount() {
		return $this->displayStockCount;
	}
	function setDisplayStockCount($displayStockCount) {
		$this->displayStockCount = $displayStockCount;
	}

	function getIgnoreStock() {
		return $this->ignoreStock;
	}
	function setIgnoreStock($ignoreStock) {
		$this->ignoreStock = $ignoreStock;
	}

	function getIsHiddenStockCount(){
		return $this->isHiddenStockCount;
	}
	function setIsHiddenStockCount($isHiddenStockCount){
		$this->isHiddenStockCount = $isHiddenStockCount;
	}

	function getSearchChildItemOnListPage(){
		return $this->searchChildItemOnListPage;
	}
	function setSearchChildItemOnListPage($searchChildItemOnListPage){
		$this->searchChildItemOnListPage = $searchChildItemOnListPage;
	}

	function getSearchChildItemOnDetailPage(){
		return $this->searchChildItemOnDetailPage;
	}
	function setSearchChildItemOnDetailPage($searchChildItemOnDetailPage){
		$this->searchChildItemOnDetailPage = $searchChildItemOnDetailPage;
	}

	function getDisplayChildItem(){
		return $this->displayChildItem;
	}
	function setDisplayChildItem($displayChildItem){
		$this->displayChildItem = $displayChildItem;
	}

	function getChildItemStock(){
		return $this->childItemStock;
	}
	function setChildItemStock($childItemStock){
		$this->childItemStock = $childItemStock;
	}

	function getNoChildItemStock(){
		return $this->noChildItemStock;
	}
	function setNoChildItemStock($noChildItemStock){
		$this->noChildItemStock = $noChildItemStock;
	}

	function getDisplayCancelOrder(){
		return $this->displayCancelOrder;
	}
	function setDisplayCancelOrder($displayCancelOrder){
		$this->displayCancelOrder = $displayCancelOrder;
	}

	function getCheckPreOrder(){
		return $this->checkPreOrder;
	}
	function setCheckPreOrder($checkPreOrder){
		$this->checkPreOrder = $checkPreOrder;
	}

	function getDestroyTrackingNumberOnCancelOrder(){
		return $this->destroyTrackingNumberOnCancelOrder;
	}
	function setDestroyTrackingNumberOnCancelOrder($destroyTrackingNumberOnCancelOrder){
		$this->destroyTrackingNumberOnCancelOrder = $destroyTrackingNumberOnCancelOrder;
	}

	function getMultiSelectStatusMode(){
		return $this->multiSelectStatusMode;
	}
	function setMultiSelectStatusMode($multiSelectStatusMode){
		$this->multiSelectStatusMode = $multiSelectStatusMode;
	}

	function getOrderCSVExportLimit(){
		return $this->orderCSVExportLimit;
	}
	function setOrderCSVExportLimit($orderCSVExportLimit){
		$this->orderCSVExportLimit = $orderCSVExportLimit;
	}

	function getCartPageTimeLimit(){
		return $this->cartPageTimeLimit;
	}
	function setCartPageTimeLimit($cartPageTimeLimit){
		$this->cartPageTimeLimit = $cartPageTimeLimit;
	}

	function getCartTryCountAndBanByIpAddress(){
		return $this->cartTryCountAndBanByIpAddress;
	}
	function setCartTryCountAndBanByIpAddress($cartTryCountAndBanByIpAddress){
		$this->cartTryCountAndBanByIpAddress = $cartTryCountAndBanByIpAddress;
	}

	function getCartBanPeriod(){
		return $this->cartBanPeriod;
	}
	function setCartBanPeriod($cartBanPeriod){
		$this->cartBanPeriod = $cartBanPeriod;
	}

	function getDisplayPageAfterLogout(){
		return $this->displayPageAfterLogout;
	}
	function setDisplayPageAfterLogout($displayPageAfterLogout){
		$this->displayPageAfterLogout = $displayPageAfterLogout;
	}

	function getDisplaySendInformationForm(){
		return $this->displaySendInformationForm;
	}
	function setDisplaySendInformationForm($displaySendInformationForm){
		$this->displaySendInformationForm = $displaySendInformationForm;
	}

	function getAllowMailAddressLogin(){
		return $this->allowMailAddressLogin;
	}
	function setAllowMailAddressLogin($allowMailAddressLogin){
		$this->allowMailAddressLogin = $allowMailAddressLogin;
	}

	function getAllowLoginIdLogin(){
		return $this->allowLoginIdLogin;
	}
	function setAllowLoginIdLogin($allowLoginIdLogin){
		$this->allowLoginIdLogin = $allowLoginIdLogin;
	}

	function getAccountIdItemName(){
		return $this->accountIdItemName;
	}
	function setAccountIdItemName($accountIdItemName){
		$this->accountIdItemName = $accountIdItemName;
	}

	function getPasswordCount(){
		return $this->passwordCount;
	}
	function setPasswordCount($passwordCount){
		$this->passwordCount = $passwordCount;
	}

	function getDisplayUsableTagList(){
		return $this->displayUsableTagList;
	}
	function setDisplayUsableTagList($displayUsableTagList){
		$this->displayUsableTagList = $displayUsableTagList;
	}

	function getUseUserCode(){
		return $this->useUserCode;
	}
	function setUseUserCode($useUserCode){
		$this->useUserCode = $useUserCode;
	}

	function getInsertDummyMailAddress(){
		return $this->insertDummyMailAddress;
	}
	function setInsertDummyMailAddress($insertDummyMailAddress){
		$this->insertDummyMailAddress = $insertDummyMailAddress;
	}

	function getInsertDummyMailAddressOnAdmin(){
		return $this->insertDummyMailAddressOnAdmin;
	}
	function setInsertDummyMailAddressOnAdmin($insertDummyMailAddressOnAdmin){
		$this->insertDummyMailAddressOnAdmin = $insertDummyMailAddressOnAdmin;
	}

	function getInsertDummyMailAddressOnAdminRegister(){
		return $this->insertDummyMailAddressOnAdminRegister;
	}
	function setInsertDummyMailAddressOnAdminRegister($insertDummyMailAddressOnAdminRegister){
		$this->insertDummyMailAddressOnAdminRegister = $insertDummyMailAddressOnAdminRegister;
	}

	function getInsertDummyAddressOnAdmin(){
		return $this->insertDummyAddressOnAdmin;
	}
	function setInsertDummyAddressOnAdmin($insertDummyAddressOnAdmin){
		$this->insertDummyAddressOnAdmin = $insertDummyAddressOnAdmin;
	}

	// function getIsChildItemOnAdminOrder(){
	// 	return $this->isChildItemOnAdminOrder;
	// }
	// function setIsChildItemOnAdminOrder($isChildItemOnAdminOrder){
	// 	$this->isChildItemOnAdminOrder = $isChildItemOnAdminOrder;
	// }

	function getIsUnregisteredItem(){
		return $this->isUnregisteredItem;
	}
	function setIsUnregisteredItem($isUnregisteredItem){
		$this->isUnregisteredItem = $isUnregisteredItem;
	}

	function getDisplayRegisterAfterItemSearchOnAdmin(){
		return $this->displayRegisterAfterItemSearchOnAdmin;
	}
	function setDisplayRegisterAfterItemSearchOnAdmin($displayRegisterAfterItemSearchOnAdmin){
		$this->displayRegisterAfterItemSearchOnAdmin = $displayRegisterAfterItemSearchOnAdmin;
	}

	function getAddSearchChildItemNameOnAdmin(){
		return $this->addSearchChildItemNameOnAdmin;
	}
	function setAddSearchChildItemNameOnAdmin($addSearchChildItemNameOnAdmin){
		$this->addSearchChildItemNameOnAdmin = $addSearchChildItemNameOnAdmin;
	}

	function getAllowRegistrationZeroYenProducts(){
		return $this->allowRegistrationZeroYenProducts;
	}
	function setAllowRegistrationZeroYenProducts($allowRegistrationZeroYenProducts){
		$this->allowRegistrationZeroYenProducts = $allowRegistrationZeroYenProducts;
	}

	function getAllowRegistrationZeroQuantityProducts(){
		return $this->allowRegistrationZeroQuantityProducts;
	}
	function setAllowRegistrationZeroQuantityProducts($allowRegistrationZeroQuantityProducts){
		$this->allowRegistrationZeroQuantityProducts = $allowRegistrationZeroQuantityProducts;
	}

	function getChangeParentItemNameOnAdmin(){
		return $this->changeParentItemNameOnAdmin;
	}
	function setChangeParentItemNameOnAdmin($changeParentItemNameOnAdmin){
		$this->changeParentItemNameOnAdmin = $changeParentItemNameOnAdmin;
	}

	function getDisplayPurchasePriceOnAdmin(){
		return $this->displayPurchasePriceOnAdmin;
	}
	function setDisplayPurchasePriceOnAdmin($displayPurchasePriceOnAdmin){
		$this->displayPurchasePriceOnAdmin = $displayPurchasePriceOnAdmin;
	}

	function getDisplayOrderAdminPage(){
		return $this->displayOrderAdminPage;
	}
	function setDisplayOrderAdminPage($displayOrderAdminPage){
		$this->displayOrderAdminPage = $displayOrderAdminPage;
	}

	function getDisplayOrderButtonOnUserAdminPage(){
		return $this->displayOrderButtonOnUserAdminPage;
	}
	function setDisplayOrderButtonOnUserAdminPage($displayOrderButtonOnUserAdminPage){
		$this->displayOrderButtonOnUserAdminPage = $displayOrderButtonOnUserAdminPage;
	}

	function getAutoOperateAuthorId(){
		return $this->autoOperateAuthorId;
	}
	function setAutoOperateAuthorId($autoOperateAuthorId){
		$this->autoOperateAuthorId = $autoOperateAuthorId;
	}

	function getDisplayItemAdminPage(){
		return $this->displayItemAdminPage;
	}
	function setDisplayItemAdminPage($displayItemAdminPage){
		$this->displayItemAdminPage = $displayItemAdminPage;
	}

	function getDisplayUserAdminPage(){
		return $this->displayUserAdminPage;
	}
	function setDisplayUserAdminPage($displayUserAdminPage){
		$this->displayUserAdminPage = $displayUserAdminPage;
	}

	function getDefaultArea(){
		return $this->defaultArea;
	}
	function setDefaultArea($defaultArea){
		$this->defaultArea = $defaultArea;
	}

	function getDisplayUserOfficeItems(){
		return $this->displayUserOfficeItems;
	}
	function setDisplayUserOfficeItems($displayUserOfficeItems){
		$this->displayUserOfficeItems = $displayUserOfficeItems;
	}

	function getDisplayUserProfileItems(){
		return $this->displayUserProfileItems;
	}
	function setDisplayUserProfileItems($displayUserProfileItems){
		$this->displayUserProfileItems = $displayUserProfileItems;
	}

	function getInsertDummyItemCode(){
		return $this->insertDummyItemCode;
	}
	function setInsertDummyItemCode($insertDummyItemCode){
		$this->insertDummyItemCode = $insertDummyItemCode;
	}

	function getDummyItemCodeRule(){
		return $this->dummyItemCodeRule;
	}
	function setDummyItemCodeRule($dummyItemCodeRule){
		return $this->dummyItemCodeRule = $dummyItemCodeRule;
	}

	function getDisplayItemKeywords(){
		return $this->displayItemKeywords;
	}
	function setDisplayItemKeywords($displayItemKeywords){
		$this->displayItemKeywords = $displayItemKeywords;
	}

	function getDisplayItemDescription(){
		return $this->displayItemDescription;
	}
	function setDisplayItemDescription($displayItemDescription){
		$this->displayItemDescription = $displayItemDescription;
	}

	function getDisplayItemImage(){
		return $this->displayItemImage;
	}
	function setDisplayItemImage($displayItemImage){
		$this->displayItemImage = $displayItemImage;
	}

	function getIsShowOnlyAdministrator(){
		return $this->isShowOnlyAdministrator;
	}
	function setIsShowOnlyAdministrator($isShowOnlyAdministrator){
		$this->isShowOnlyAdministrator = $isShowOnlyAdministrator;
	}

	function getRequireText(){
		return $this->requireText;
	}
	function setRequireText($requireText){
		$this->requireText = $requireText;
	}

	function getMultiCategory(){
		return $this->multiCategory;
	}
	function setMultiCategory($multiCategory){
		$this->multiCategory = $multiCategory;
	}
}
