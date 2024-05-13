<?php

class UtilMultiLanguageConfigFormPage extends WebPage{
	
	function __construct(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
		SOY2::import("site_include.plugin.util_multi_language.config.LanguageListComponent");
	}
	
	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			$this->pluginObj->setConfig($_POST["Config"]);
			$check = (isset($_POST["check_browser_language"])) ? (int)$_POST["check_browser_language"] : 0;
			$this->pluginObj->setCheckBrowserLanguage($check);
			$on = (isset($_POST["same_uri_mode"]) && $_POST["same_uri_mode"] == 1);
			$this->pluginObj->setSameUriMode($on);
			
			if($on){	// データベースに新しいテーブルを追加する
				self::_createTable();
			}

			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
		}
		CMSPlugin::redirectConfigPage();
	}
	
	function execute(){
		parent::__construct();
		
		$config = $this->pluginObj->getConfig();
		
		$this->addForm("form");
		
		$this->createAdd("language_list", "LanguageListComponent", array(
			"list" => SOYCMSUtilMultiLanguageUtil::allowLanguages(),
			"config" => $config,
			"smartPrefix" => self::_getSmartPhonePrefix()
		));
		
		$this->addCheckBox("confirm_browser_language", array(
			"name" => "check_browser_language",
			"value" => 1,
			"selected" => $this->pluginObj->getCheckBrowserLanguage(),
			"label" => "確認する"
		));	

		$this->addCheckBox("same_uri_mode", array(
			"name" => "same_uri_mode",
			"value" => 1,
			"selected" => $this->pluginObj->getSameUriMode(),
			"label" => "同一テンプレートを利用する"
		));	
	}
	
	private function _getSmartPhonePrefix(){
		//携帯振り分けプラグインがアクティブかどうか
		if(!CMSPlugin::activeCheck("util_mobile_check")) return null;
		
		$obj = CMSPlugin::loadPluginConfig("UtilMobileCheckPlugin");
		if(is_null($obj)) $obj = new UtilMobileCheckPlugin;
		return $obj->smartPrefix;
	}

	private function _createTable(){
		$dao = new SOY2DAO();

		$isInit = true;
		try{
			$_exist = $dao->executeQuery("SELECT * FROM MultiLanguageEntryRelation", array());
			$isInit = false;
		}catch(Exception $e){
			//
		}

		if($isInit){
			$file = file_get_contents(dirname(__DIR__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
			$sqls = preg_split('/CREATE TABLE/', $file, -1, PREG_SPLIT_NO_EMPTY) ;
			
			foreach($sqls as $sql){
				$sql = "create table " . trim($sql);
				try{
					$dao->executeQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}

		// ページカスタムフィールド
		$isInit = true;
		try{
			$exist = $dao->executeQuery("SELECT * FROM PageAttribute", array());
			$isInit = false;
		}catch(Exception $e){
			//
		}

		if($isInit){
			$file = file_get_contents(dirname(dirname(dirname(__FILE__))) . "/PageCustomField/sql/init_".SOYCMS_DB_TYPE.".sql");
			$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;
	
			foreach($sqls as $sql){
				$sql = trim("create" . $sql);
				try{
					$dao->executeUpdateQuery($sql, array());
				}catch(Exception $e){
					//
				}
			}
		}
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
