<?php

class UtilMultiLanguageConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
	}

	function doPost(){

		if(soy2_check_token() && isset($_POST["Config"])){
			UtilMultiLanguageUtil::saveConfig($_POST["Config"]);

			//英語用のテンプレートが無ければここでテンプレートを生成する
			self::_makeTemplate();

			//スマホ版の英語用のテンプレートを作成
			if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
				self::_makeTemplate(UtilMultiLanguageUtil::MODE_SMARTPHONE);
			}
			$this->config->redirect("updated");
		}
	}

	private function _makeTemplate(string $mode=UtilMultiLanguageUtil::MODE_PC){
		foreach(UtilMultiLanguageUtil::getConfig() as $key => $values){
			if(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE){
				if(isset($values["prefix"]) && strlen($values["prefix"]) > 0){
					self::_copyCartTemplate($values["prefix"], $mode);
					self::_copyMypageTemplate($values["prefix"], $mode);
				}
			}
		}
	}

	private function _copyCartTemplate(string $language, string $mode){
		$cartId = ($mode == UtilMultiLanguageUtil::MODE_PC) ? SOYShop_DataSets::get("config.cart.cart_id", "bryon") : SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
		if($cartId === "none") return;	// カートIDがnoneの場合は何もしない
		
		self::_copyApplicationTemplate($language, $cartId, "cart");

		/** システム側のテンプレートも用意 */
		self::_copyPageDir($language, $cartId, "cart");
	}

	private function _copyMypageTemplate(string $language, string $mode){
		$mypageId = ($mode == UtilMultiLanguageUtil::MODE_PC) ? SOYShop_DataSets::get("config.mypage.id", "bryon") : SOYShop_DataSets::get("config.mypage.smartphone.id", "smart");
		if($mypageId === "none") return;	// マイページIDがnoneの場合は何しない

		self::_copyApplicationTemplate($language, $mypageId, "mypage");

		/** システム側のテンプレートも用意 */
		self::_copyPageDir($language, $mypageId, "mypage");

	}

	private function _copyApplicationTemplate(string $language, string $appId, string $mode="cart"){
		$dir = SOYSHOP_SITE_DIRECTORY . ".template/" . $mode . "/";

		if(!file_exists($dir . $appId . "_" . $language . ".ini")){
			copy($dir . $appId . ".html", $dir . $appId . "_" . $language . ".html");
			copy($dir . $appId . ".ini", $dir . $appId . "_" . $language . ".ini");

			$iniFile = file_get_contents($dir . $appId . "_" . $language . ".ini");
			$iniFile = str_replace($appId, $appId . "_" . $language, $iniFile);
			file_put_contents($dir . $appId . "_" . $language . ".ini", $iniFile);
		}
	}

	private function _copyPageDir(string $language, string $appId, string $mode){
		$dir = SOY2::RootDir() . $mode . "/";
		$tmpDir = SOYSHOP_SITE_DIRECTORY . ".template/" . $mode . "/";
		if(file_exists($dir . $appId) && !file_exists($tmpDir . $appId . "_" . $language)){
			mkdir($tmpDir . $appId . "_" . $language);
			$oldDir = $dir . $appId . "/";
			$newDir = $tmpDir . $appId . "_" . $language . "/";
			self::_copyFileRecursive($oldDir, $newDir);
		}
	}

	private function _copyFileRecursive(string $oldDir, string $newDir){
		if(is_dir($oldDir) && is_readable($oldDir)){
			$files = scandir($oldDir);
			foreach($files as $file){
				if($file[0] == ".") continue;
				if(soy2_strpos($file, ".php") > 0) continue;
				
				if(strpos($file, ".html")){
					$tmpDir = substr($newDir, 0, strrpos($newDir, "pages/"));
					copy($oldDir . $file, $tmpDir . $file);
                    //ディレクトリの場合
				}elseif(is_dir($oldDir . $file)){
					mkdir($newDir . $file);
					self::_copyFileRecursive($oldDir . $file . "/", $newDir . $file . "/");
				}
			}
		}
	}

	function execute(){

		$config = UtilMultiLanguageUtil::getConfig();

		//ページの追加ボタン
		if(isset($_GET["create"])){
			SOY2Logic::createInstance("module.plugins.util_multi_language.logic.CreatePageLogic")->create();
			$this->config->redirect("created");
		}

		parent::__construct();

		DisplayPlugin::toggle("update", (isset($_GET["updated"])));
		DisplayPlugin::toggle("created", (isset($_GET["created"])));
		DisplayPlugin::toggle("annotation", !SOYShopPluginUtil::checkIsActive("bulk_page_remove_plugin"));

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
