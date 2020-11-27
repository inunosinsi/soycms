<?php

class CustomfieldMigrationConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.customfield_migration.component.CustomfieldConfigListComponent");
		SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");
		if(!class_exists("CustomField")) include_once(SOY2::RootDir() . "site_include/plugin/CustomField/entity.php");
		SOY2::import("site_include.plugin.customfield_migration.util.CustomfieldMigrationUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["migrate"])){
				//全記事を丁寧に取得する
				$entryIds = CustomfieldMigrationUtil::getAllEntryIds();
				if(!count($entryIds)) CMSPlugin::redirectConfigPage();

				$cnf = CustomfieldMigrationUtil::getConfig();	//プラグインの方の設定
				$cfCnf = CustomfieldMigrationUtil::migrateConfig($cnf["CustomField"]);
				$cfaCnf = CustomfieldMigrationUtil::migrateConfig($cnf["CustomFieldAdvanced"]);

				if(!count($cfCnf) && !count($cfaCnf)) CMSPlugin::redirectConfigPage();

				set_time_limit(0);

				$isCfCnf = (count($cfCnf));
				$isCfaCnf = (count($cfaCnf));

				$dbLogic = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic");

				foreach($entryIds as $entryId){
					$values = array();		//カスタムサーチフィールドへ移行するデータを格納する配列

					if($isCfCnf){
						$customfields = CustomfieldMigrationUtil::getCustomfieldValuesByEntryId($entryId);
						if(count($customfields)){
							foreach($customfields as $fieldId => $cf){
								if(isset($cfCnf[$fieldId])){
									$values[$cfCnf[$fieldId]] = $cf->getValue();
								}
							}
						}
					}

					if($isCfaCnf){
						$customfields = CustomfieldMigrationUtil::getCustomfieldAdvancedValuesByEntryId($entryId);
						if(count($customfields)){
							foreach($customfields as $fieldId => $cf){
								if(isset($cfaCnf[$fieldId])){
									$values[$cfaCnf[$fieldId]] = $cf->getValue();
								}
							}
						}
					}

					//移行する値がなにもない場合は次の記事へ
					if(!count($values) || !self::_checkIsValue($values)) continue;

					// @ToDo カスタムサーチフィールド側のフィールドの型を調べて、その型に合うようにデータを整形する

					$dbLogic->save($entryId, $values);
				}

				CMSPlugin::redirectConfigPage();

			}else if(isset($_POST["update"])){
				$cfCnf = (isset($_POST["CustomField"])) ? $_POST["CustomField"] : array();
				$cfaCnf = (isset($_POST["CustomFieldAdvanced"])) ? $_POST["CustomFieldAdvanced"] : array();
				CustomfieldMigrationUtil::saveConfig($cfCnf, $cfaCnf);

				CMSPlugin::redirectConfigPage();
			}
		}
	}

	private function _checkIsValue($values){
		foreach($values as $v){
			if(strlen($v)) return true;
		}
		return false;
	}

	function execute(){
		parent::__construct();

		//カスタムフィールドの設定
		$customfields = self::_getCustomfieldConfig();
		$cfCnfCnt = count($customfields);

		//カスタムフィールドアドバンスド
		$customfieldAdvanced = self::_getCustomfieldConfig("Advanced");
		$cfaCnfCnt = count($customfieldAdvanced);

		//カスタムサーチフィールド
		$customSearchFields = self::_getCustomSearchFieldConfig();
		$csfCnfCnt = count($customSearchFields);

		DisplayPlugin::toggle("no_custom_search_config", $csfCnfCnt === 0);
		DisplayPlugin::toggle("is_custom_search_config", $csfCnfCnt > 0);

		//カスタムフィールドとカスタムフィールドアドバンスドの設定がなければ使用出来ない。
		DisplayPlugin::toggle("not_usable_plugin", ($cfCnfCnt === 0 && $cfaCnfCnt === 0));
		DisplayPlugin::toggle("usable_plugin", ($cfCnfCnt > 0 || $cfaCnfCnt > 0));

		//データ移行実行ボタン
		$this->addForm("migrate_form");


		$this->addForm("form");

		$cnf = CustomfieldMigrationUtil::getConfig();	//プラグインの方の設定
		$csfOpts = ($csfCnfCnt) ? self::_migrateScfCnf($customSearchFields) : array();

		//カスタムフィールド
		DisplayPlugin::toggle("usable_customfield", $cfCnfCnt);

		$this->createAdd("customfield_config_list", "CustomfieldConfigListComponent", array(
			"list" => $customfields,
			"csfOpts" => $csfOpts,
			"mode" => "CustomField",
			"config" => (isset($cnf["CustomField"])) ? $cnf["CustomField"] : array()
		));

		//カスタムフィールド
		DisplayPlugin::toggle("usable_customfield_advanced", $cfaCnfCnt);

		$this->createAdd("customfield_advanced_config_list", "CustomfieldConfigListComponent", array(
			"list" => $customfieldAdvanced,
			"csfOpts" => $csfOpts,
			"mode" => "CustomFieldAdvanced",
			"config" => (isset($cnf["CustomFieldAdvanced"])) ? $cnf["CustomFieldAdvanced"] : array()
		));
	}

	private function _getCustomfieldConfig($postfix=""){
		$fname = UserInfoUtil::getSiteDirectory() . '.plugin/CustomField' .$postfix . '.config';
		if(file_exists($fname)){
			SOY2::import("site_include.plugin.CustomField.CustomField" . $postfix, ".php");
			$obj = unserialize(file_get_contents($fname));
			return $obj->customFields;
		}else{
			return array();
		}
	}

	private function _getCustomSearchFieldConfig(){
		return CustomSearchFieldUtil::getConfig();
	}

	private function _migrateScfCnf($csfCnf){
		$opts = array();
		foreach($csfCnf as $fieldId => $cnf){
			$opts[$fieldId] = $cnf["label"] . " (csf=\"" . $fieldId . "\")【" . CustomSearchFieldUtil::getTypeText($cnf["type"]) . "】";
		}
		return $opts;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
