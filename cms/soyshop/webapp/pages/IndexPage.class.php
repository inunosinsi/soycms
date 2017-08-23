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
		
		//何でもできる拡張ポイント
		SOYShopPlugin::load("soyshop.admin");
		SOYShopPlugin::invoke("soyshop.admin");
	}

	function __construct(){
		MessageManager::addMessagePath("admin");

		parent::__construct();
		
		//データベースの更新を調べる
		$checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		$hasCheckVer = $checkVersionLogic->checkVersion();
		DisplayPlugin::toggle("has_db_update", $hasCheckVer);
		
		//データベースの更新終了時に表示する
		$doUpdated = (isset($_GET["update"]) && $_GET["update"] == "finish");
		DisplayPlugin::toggle("do_db_update", $doUpdated);
		
		DisplayPlugin::toggle("display_db_update_area", ($hasCheckVer || $doUpdated));
		
		$this->action();

		self::buildPluginArea();
		
		$this->addModel("init_link", array(
			"visible" => DEBUG_MODE
		));
	}
	
	private function buildPluginArea(){
		SOYShopPlugin::load("soyshop.admin.top");
		$delegate = SOYShopPlugin::invoke("soyshop.admin.top");

		$contents = $delegate->getContents();
		DisplayPlugin::toggle("plugin_area", (count($contents) > 0));
		
		$this->createAdd("plugin_area_list", "_common.TopPagePluginAreaListComponent", array(
			"list" => $contents
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