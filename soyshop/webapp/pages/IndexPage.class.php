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

		//何でもできる拡張ポイント
		SOYShopPlugin::load("soyshop.admin");
		SOYShopPlugin::invoke("soyshop.admin");
	}

	function __construct(){
		MessageManager::addMessagePath("admin");
		SOYShopPlugin::load("soyshop.admin.top");

		//バージョンアップ時のキャッシュの自動削除
		$cacheLogic = SOY2Logic::createInstance("logic.cache.CacheLogic");
		if($cacheLogic->checkCacheVersion()) $cacheLogic->clearCache();

		parent::__construct();

		//データベースの更新を調べる
		$checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		$hasCheckVer = $checkVersionLogic->checkVersion();
		DisplayPlugin::toggle("has_db_update", $hasCheckVer);

		//データベースの更新終了時に表示する
		$doUpdated = (isset($_GET["update"]) && $_GET["update"] == "finish");
		DisplayPlugin::toggle("do_db_update", $doUpdated);

		DisplayPlugin::toggle("display_db_update_area", ($hasCheckVer || $doUpdated));

		//noticeの拡張ポイント
		$notices = SOYShopPlugin::invoke("soyshop.admin.top", array("mode" => "notice"))->getContents();
		if(is_null($notices)) $notices = array();

		$this->createAdd("notice_list", "_common.TopPageNoticeListComponent", array(
			"list" => $notices,
			"mode" => "success"
		));

		//errorの拡張ポイント
		$errors = SOYShopPlugin::invoke("soyshop.admin.top", array("mode" => "error"))->getContents();
		if(is_null($errors)) $errors = array();

		$this->createAdd("error_list", "_common.TopPageNoticeListComponent", array(
			"list" => $errors,
			"mode" => "danger"
		));

		$this->action();


		self::buildPluginArea();

		//便利な機能
		DisplayPlugin::toggle("init_link", DEBUG_MODE);
		$this->createAdd("init_link_list", "_common.InitLinkListComponent", array(
			"list" => self::getInitLinks()
		));
	}

	private function getInitLinks(){
		$list = SOYShopPlugin::invoke("soyshop.admin", array(
			"mode" => "init"
		))->getList();
		if(!count($list)) return array();

		$array = array();
		foreach ($list as $moduleId => $vals){
			if(!is_array($vals) || !count($vals)) continue;
			foreach($vals as $v){
				if(!isset($v["label"]) || !strlen($v["label"])) continue;
				$array[] = $v;
			}
		}
		return $array;
	}

	private function buildPluginArea(){
		$contents = SOYShopPlugin::invoke("soyshop.admin.top")->getContents();
		if(is_null($contents)) $contents = array();
		DisplayPlugin::toggle("plugin_area", (count($contents) > 0));

		$this->createAdd("plugin_area_list", "_common.TopPagePluginAreaListComponent", array(
			"list" => $contents
		));
	}
}
