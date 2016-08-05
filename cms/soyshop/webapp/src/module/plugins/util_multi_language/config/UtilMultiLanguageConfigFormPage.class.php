<?php

class UtilMultiLanguageConfigFormPage extends WebPage{
	
	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			UtilMultiLanguageUtil::saveConfig($_POST["Config"]);
			
			//英語用のテンプレートが無ければここでテンプレートを生成する
			self::makeTemplate();
			
			//スマホ版の英語用のテンプレートを作成
			if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
				self::makeTemplate(UtilMultiLanguageUtil::MODE_SMARTPHONE);
			}
			$this->config->redirect("updated");
		}
	}
	
	private function makeTemplate($mode = UtilMultiLanguageUtil::MODE_PC){
		foreach(UtilMultiLanguageUtil::getConfig() as $key => $values){
			if(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE){
				if(isset($values["prefix"]) && strlen($values["prefix"]) > 0){
					self::copyCartTemplate($values["prefix"], $mode);
					self::copyMypageTemplate($values["prefix"], $mode);
				}
			}
		}
	}
	
	private function copyCartTemplate($language, $mode){
		$cartId = ($mode == UtilMultiLanguageUtil::MODE_PC) ? SOYShop_DataSets::get("config.cart.cart_id", "bryon") : SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
		self::copyApplicationTemplate($language, $cartId, "cart");
		
		/** システム側のテンプレートも用意 */	
		self::copyPageDir($language, $cartId, "cart");
	}
	
	private function copyMypageTemplate($language, $mode){
		$mypageId = ($mode == UtilMultiLanguageUtil::MODE_PC) ? SOYShop_DataSets::get("config.mypage.id", "bryon") : SOYShop_DataSets::get("config.mypage.smartphone.id", "smart");
		self::copyApplicationTemplate($language, $mypageId, "mypage");
		
		/** システム側のテンプレートも用意 */
		self::copyPageDir($language, $mypageId, "mypage");
		
	}
	
	private function copyApplicationTemplate($language, $appId, $mode = "cart"){
		$dir = SOYSHOP_SITE_DIRECTORY . ".template/" . $mode . "/";
		
		if(!file_exists($dir . $appId . "_" . $language . ".ini")){
			copy($dir . $appId . ".html", $dir . $appId . "_" . $language . ".html");
			copy($dir . $appId . ".ini", $dir . $appId . "_" . $language . ".ini");
			
			$iniFile = file_get_contents($dir . $appId . "_" . $language . ".ini");
			$iniFile = str_replace($appId, $appId . "_" . $language, $iniFile);
			file_put_contents($dir . $appId . "_" . $language . ".ini", $iniFile);
		}
	}
	
	private function copyPageDir($language, $appId, $mode){
		$dir = SOY2::RootDir() . $mode . "/";
		if(file_exists($dir . $appId) && !file_exists($dir . $appId . "_" . $language)){
			mkdir($dir . $appId . "_" . $language);
			$oldDir = $dir . $appId . "/";
			$newDir = $dir . $appId . "_" . $language . "/";
			self::copyFileRecursive($oldDir, $newDir);
		}
	}
	
	private function copyFileRecursive($oldDir, $newDir){
		if(is_dir($oldDir) && is_readable($oldDir)){
			$files = scandir($oldDir);
			foreach($files as $file){
				if($file[0] == ".") continue;
				
				if(strpos($file, ".php") || strpos($file, ".html")){
					copy($oldDir . $file, $newDir . $file);
                    //ディレクトリの場合
				}elseif(is_dir($oldDir . $file)){
					mkdir($newDir . $file);
					self::copyFileRecursive($oldDir . $file . "/", $newDir . $file . "/");
				}
			}
		}
	}
	
	function execute(){
		
		$config = UtilMultiLanguageUtil::getConfig();
		
		//ページの追加ボタン
		if(isset($_GET["create"])){
			$pageLogic = SOY2Logic::createInstance("module.plugins.util_multi_language.logic.CreatePageLogic");
			$pageLogic->create();
			$this->config->redirect("created");
		}
		
		WebPage::WebPage();
		
		DisplayPlugin::toggle("update", (isset($_GET["updated"])));
		DisplayPlugin::toggle("created", (isset($_GET["created"])));
		
		$this->addForm("form");
		
		SOY2::import("module.plugins.util_multi_language.config.LanguageListComponent");
		$this->createAdd("language_list", "LanguageListComponent", array(
			"list" => UtilMultiLanguageUtil::allowLanguages(true),
			"config" => $config
		));
		
		$this->addCheckBox("confirm_browser_language_config", array(
			"name" => "Config[check_browser_language_config]",
			"value" => UtilMultiLanguageUtil::IS_USE,
			"selected" => (isset($config["check_browser_language_config"])) ? (int)$config["check_browser_language_config"] : 0,
			"label" => "確認する"
		));

        $this->addCheckBox("first_access_config", array(
            "name" => "Config[check_first_access_config]",
            "value" => UtilMultiLanguageUtil::IS_USE,
            "selected" => (isset($config["check_first_access_config"])) ? (int)$config["check_first_access_config"] : 0,
            "label" => "初回アクセスのみ確認する"
        ));
	}
	
	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>