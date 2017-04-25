<?php

class IndexPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token() && is_null(STEPMAIL_SHOP_ID) && isset($_POST["Init"]["siteId"])){
			$txt = "<?php\ndefine(STEPMAIL_SHOP_ID, \"" . $_POST["Init"]["siteId"] . "\");\n?>";
			file_put_contents(dirname(dirname(__FILE__)) . "/shop_id.php", $txt);
			
			CMSApplication::jump("");
		}
	}
	
	function __construct(){
		WebPage::__construct();
		
		self::displayInitArea();
		
		DisplayPlugin::toggle("connected_shop_site", !is_null(STEPMAIL_SHOP_ID));
	}
	
	private function displayInitArea(){
		//ショップサイトとの連携が行われていない時に表示するエラー
		DisplayPlugin::toggle("no_connected_shop_site", is_null(STEPMAIL_SHOP_ID));
		
		if(is_null(STEPMAIL_SHOP_ID)){
			$shopList = SOY2Logic::createInstance("logic.Init.InitLogic")->getSOYShopSiteList();
		}else{
			$shopList = array();
		}
		
		DisplayPlugin::toggle("no_shop_site", !count($shopList));
		DisplayPlugin::toggle("has_shop_site", count($shopList));
		
		$this->addForm("form");
		
		$this->addSelect("shop_list", array(
			"name" => "Init[siteId]",
			"options" => $shopList
		));
	}
}
?>