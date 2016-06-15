<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
SOY2::import("domain.order.SOYShop_ItemModule");
SOY2::import("domain.config.SOYShop_ShopConfig");

class IndexPage extends WebPage{

	private $pluginDao;
	private $itemDao;
	private $userDao;
	private $orderDao;
	private $itemOrderDao;
	private $config;
	private $appLimit;	//管理制限者の場合、false

	function doPost(){

	}

	function action(){
		
		if(DEBUG_MODE && isset($_GET["init_db"])){

			SOY2Logic::createInstance("logic.init.InitLogic")->initDB();

			SOY2PageController::jump("");
		}

		if(DEBUG_MODE && isset($_GET["init_template"])){

			SOY2Logic::createInstance("logic.init.InitLogic")->initDefaultTemplate(SOYSHOP_SITE_DIRECTORY . ".template/");
			SOY2PageController::jump("");

		}

		if(DEBUG_MODE && isset($_GET["init_theme"])){

			SOY2Logic::createInstance("logic.init.InitLogic")->initDefaultTheme(SOYSHOP_SITE_DIRECTORY . "themes/");
			SOY2PageController::jump("");
		}


		if(DEBUG_MODE && isset($_GET["init_mail"])){
			SOY2Logic::createInstance("logic.init.InitPageLogic")->initDefaultMail();
			SOY2PageController::jump("");
		}

		if(isset($_GET["clear_cache"])){
			$dir = SOYSHOP_SITE_DIRECTORY . "/.cache/";
			$files = scandir($dir);
			foreach($files as $file){
				if($file[0] == ".") continue;
				@unlink($dir . $file);
			}
			SOY2PageController::jump("");
		}

		//upgrade
		if(isset($_GET["upgrade"])){
			SOY2::import("logic.upgrade.UpgradeLogic");

			$ver = $_GET["upgrade"];
			$logic = SOY2Logic::createInstance("logic.upgrade.UpgradeLogic", array(
				"version" => $ver
			));

			$logic->upgrade();

			SOY2PageController::jump("");
		}
	}

	function IndexPage(){
		MessageManager::addMessagePath("admin");
		
		//管理制限の権限を取得
		$session = SOY2ActionSession::getUserSession();
		$this->appLimit = $session->getAttribute("app_shop_auth_limit");

		//SOY Shopの基本設定
		$this->config = SOYShop_ShopConfig::load();

		//DAOの読み込み
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$this->itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		WebPage::WebPage();
		
		//データベースの更新を調べる
		$checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		DisplayPlugin::toggle("has_db_update", $checkVersionLogic->checkVersion());
		
		//データベースの更新終了時に表示する
		DisplayPlugin::toggle("do_db_update", (isset($_GET["update"]) && $_GET["update"] == "finish"));
		
		$this->action();


		self::buildOrderList();
		self::buildCampaignList();
		self::buildCouponHistoryList();
		self::buildAutoRankingList();
		self::buildNoticeArrivalList();
		self::buildStockList();
		self::buildReviewsList();
		self::buildNewsList();
		self::buildRecommendList();
		self::buildInfoBlock();
		self::buildItemList();
		self::buildPageList();

		$this->addModel("init_link", array(
			"visible" => DEBUG_MODE
		));
	}

	private function buildOrderList(){

		$this->orderDao->setLimit(16);
		try{
			$orders = $this->orderDao->getByStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
		}catch(Exception $e){
			$orders = array();
		}

		DisplayPlugin::toggle("more_order", (count($orders) > 15));
		DisplayPlugin::toggle("has_order", (count($orders) > 0));
		DisplayPlugin::toggle("no_order", (count($orders) === 0));

		$orders = array_slice($orders, 0, 15);

		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders
		));
	}
	
	private function buildCampaignList(){
		
		$isActive = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("campaign")));
		
		DisplayPlugin::toggle("campaign", $isActive);
		
		if($isActive){
			SOY2::imports("module.plugins.campaign.domain.*");
			$campaigns = SOY2DAOFactory::create("SOYShop_CampaignDAO")->getBeforePostPeriodEnd(6);
		}else{
			$campaigns = array();
		}
		
		DisplayPlugin::toggle("more_campaign", (count($campaigns) > 5));
		DisplayPlugin::toggle("has_campaign", (count($campaigns) > 0));
		DisplayPlugin::toggle("no_campaign", (count($campaigns) === 0));
		
		$campaigns = array_slice($campaigns, 0, 5);
		
		SOY2::imports("module.plugins.campaign.component.*");
		$this->createAdd("campaign_list", "CampaignListComponent", array(
			"list" => $campaigns
		));
	}
	
	private function buildCouponHistoryList(){
		
		DisplayPlugin::toggle("coupon_history", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("discount_free_coupon"))));		
		
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::imports("module.plugins.discount_free_coupon.logic.*");
		
		$couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$couponHistoryDao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");
		$couponHistoryDao->setLimit(6);
		
		try{
			$histories = $couponHistoryDao->get();
		}catch(Exception $e){
			$histories = array();
		}
		
		DisplayPlugin::toggle("more_coupon_history", (count($histories) > 5));
		DisplayPlugin::toggle("has_coupon_history", (count($histories) > 0));
		DisplayPlugin::toggle("no_coupon_history", (count($histories) === 0));
		
		$histories = array_slice($histories, 0, 5);
				
		$this->createAdd("coupon_history_list", "_common.Coupon.CouponHistoryComponent", array(
			"list" => $histories,
			"userDao" => $this->userDao,
			"orderDao" => $this->orderDao,
			"couponDao" => $couponDao
		));
		
	}
	
	private function buildAutoRankingList(){
		
		$isActive = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_auto_ranking")));
		
		DisplayPlugin::toggle("auto_ranking", $isActive);
		
		$items = array();
		$latestDate = null;
		
		if($isActive){
			$displayLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.DisplayRankingLogic");
			$items = $displayLogic->getItems();
			$items = array_slice($items, 0, 5);
			
			$latestDate = $displayLogic->getLatestCalcDate();
		}
		
		if($latestDate){
			$calcMessage = "最終集計日時は" . date("Y-m-d H:i:s", $latestDate) . "です。";
		}else{
			$calcMessage = "集計されていません。";
		}
		
		$this->addLabel("latest_calc_date", array(
			"text" => $calcMessage
		));

		DisplayPlugin::toggle("has_auto_ranking", (count($items) > 0));
		DisplayPlugin::toggle("no_auto_ranking", (count($items) === 0));

		$this->createAdd("auto_ranking_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => $this->config,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => $this->itemOrderDao
		));
	}
	
	private function buildNoticeArrivalList(){
		$isActive = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_notice_arrival")));
		
		$users = array();
		if($isActive){
			$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			$users = $noticeLogic->getUsersForNewsPage(SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
		}
		
		DisplayPlugin::toggle("notice_arrival", $isActive);
		DisplayPlugin::toggle("has_notice_arrival", (count($users) > 0));
		DisplayPlugin::toggle("no_notice_arrival", (count($users) === 0));

		$this->createAdd("notice_arrival_list", "_common.Plugin.NoticeArrivalListComponent", array(
			"list" => $users
		));
	}

	private function buildStockList(){
		
		DisplayPlugin::toggle("stock", (($this->config->getDisplayStock()) > 0 && ($this->config->getIgnoreStock()) == 0));

		$this->itemDao->setLimit(6);
		try{
			$items = $this->itemDao->getByStock($this->config->getDisplayStockCount());
		}catch(Exception $e){
			$items = array();
		}

		DisplayPlugin::toggle("more_stock", (count($items) > 5));
		DisplayPlugin::toggle("has_stock", (count($items) > 0));
		DisplayPlugin::toggle("no_stock", (count($items) === 0));
		
		$items = array_slice($items, 0, 5);

		$this->createAdd("stock_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => $this->config,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => $this->itemOrderDao
		));
	}

	private function buildReviewsList(){
		
		DisplayPlugin::toggle("reviews", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("item_review"))));

		SOY2::imports("module.plugins.item_review.domain.*");
		SOY2::imports("module.plugins.item_review.logic.*");

		$reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$reviewDao->setLimit(6);

		try{
			$reviews = $reviewDao->get();
		}catch(Exception $e){
			$reviews = array();
		}

		DisplayPlugin::toggle("more_reviews", (count($reviews) > 5));
		DisplayPlugin::toggle("has_reviews", (count($reviews) > 0));
		DisplayPlugin::toggle("no_reviews", (count($reviews) === 0));

		$reviews = array_slice($reviews, 0, 5);

		$this->createAdd("reviews_list", "_common.Review.ReviewListComponent", array(
			"list" => $reviews,
			"itemDao" => $this->itemDao
		));
	}

	private function buildNewsList(){

		DisplayPlugin::toggle("news", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_simple_news"))));

		$news = SOYShop_DataSets::get("plugin.simple_news", array());

		DisplayPlugin::toggle("has_news", (count($news) > 0));
		DisplayPlugin::toggle("no_news", (count($news) === 0));

		$this->createAdd("news_list", "_common.Plugin.NewsListComponent", array(
			"list" => $news
		));
	}

	private function buildRecommendList(){

		DisplayPlugin::toggle("recommend", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_recommend_item"))));

		$itemIds = SOYShop_DataSets::get("item.recommend_items", array());
		
		$items = array();
		foreach($itemIds as $itemId){
			try{
				$items[] = $this->itemDao->getById($itemId);
			}catch(Exception $e){
				continue;
			}
		}
		$this->createAdd("recommend_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => $this->config,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => $this->itemOrderDao
		));

		DisplayPlugin::toggle("has_recommend", (count($items) > 0));
		DisplayPlugin::toggle("no_recommend", (count($items) === 0));
	}

	private function buildInfoBlock(){
		
		//管理制限者の場合、表示させない
		DisplayPlugin::toggle("info", $this->appLimit);

		$this->addLabel("shop_name", array(
			"text" => $this->config->getShopName()
		));

		$this->addLink("shop_url", array(
			"text" => soyshop_get_site_url(true),
			"link" => soyshop_get_site_url(true)
		));
	}

	private function buildItemList(){

		//管理制限者の場合、表示させない
		DisplayPlugin::toggle("update_item", $this->appLimit);

		$this->itemDao->setLimit(5);
		try{
			$items = $this->itemDao->newItems();
		}catch(Exception $e){
			$items = array();
		}

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => $this->config,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => $this->itemOrderDao
		));
	}

	private function buildPageList(){

		//管理制限者の場合、表示させない
		DisplayPlugin::toggle("update_page", $this->appLimit);

		$pageDAO = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$pageDAO->setLimit(5);

		try{
			$pages = $pageDAO->newPages();
		}catch(Exception $e){
			$pages = array();
		}
		

		$this->createAdd("page_list", "_common.PageListComponent", array(
			"list" => $pages
		));
	}

	 function getSubMenu(){
		$key = "_common.TopPageSubMenu";

		try{
			$subMenuPage = SOY2HTMLFactory::createInstance($key, array());
			return $subMenuPage->getObject();
		}catch(Exception $e){
			return null;
		}
	}
}
?>