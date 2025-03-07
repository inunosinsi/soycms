<?php
class CustomFieldBulkChangePlugin{

	const PLUGIN_ID = "CustomFieldBulkChange";
	const DEBUG = false;

	// カスタムフィールドの絞り込み
	const MODE_ALL = 0;	//値による絞り込みを行わない
	const MODE_HAS = 1;	//値があるものを絞り込む
	const MODE_NONE = 2;	//値がないものを絞り込む

	// 記事の公開状態
	const PUBLISH_ALL = 0;
	const IS_PUBLISH = 1;
	const NO_PUBLISH = 2;

	const ENTRY_LIMIT = 10000;	// @ToDo 要望があれば件数の指定の機能を設ける

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"カスタムフィールド一括変更プラグイン",
			"type" => Plugin::TYPE_DB,
			"description"=>"カスタムフィールドの値を一括で変更します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"saito@saitodev.co",
			"version"=>"0.0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			if(defined("_SITE_ROOT_")){	//公開側
				//JSONの出力
				CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
			}
		}
	}

	private function _output(array $arr=array()){
		//if(!isset($arr["entries"])) $arr["entries"] = array();
		if(self::DEBUG){
			var_dump($arr);
		}else{
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($arr);
		}
		exit;
	}

	function onSiteAccess($obj){
		if(!isset($_SERVER["PATH_INFO"]) || is_bool(strpos($_SERVER["PATH_INFO"], ".json")) || is_bool(strpos($_SERVER["PATH_INFO"], self::PLUGIN_ID.".json"))) return;
		if(!isset($_GET["label_id"]) || !is_numeric($_GET["label_id"])) return;
		if(!isset($_GET["field_id"]) || !strlen(trim($_GET["field_id"]))) return;

		$labelId = (int)$_GET["label_id"];
		$fieldId = trim($_GET["field_id"]);
		$mode = (isset($_GET["mode"])) ? (int)$_GET["mode"] : self::MODE_ALL;
		$pub = (isset($_GET["pub"])) ? (int)$_GET["pub"] : self::PUBLISH_ALL;
		$lim = (isset($_GET["lim"]) && is_numeric($_GET["lim"])) ? (int)$_GET["lim"] : self::ENTRY_LIMIT;
		
		// 以後の各メソットでEntryクラスを利用する
		if(!class_exists("Entry")) SOY2::import("domain.cms.Entry");
		$dao = new SOY2DAO();
		
		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT id, title, isPublished FROM Entry ".
				"WHERE id IN (".
					"SELECT entry_id FROM EntryLabel ".
					"WHERE label_id = :labelId".
				")",
				array(":labelId" => $labelId)
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) self::_output();

		$entryIds = self::_entryIds($res);
		$customfieldValues = self::_getCustomFieldValues($fieldId, $entryIds);
		
		$_arr = array("entries" => array());

		foreach($res as $v){
			if(count($_arr) > $lim) break;
		
			$entryId = (int)$v["id"];
			$customfieldValue = (isset($customfieldValues[$entryId])) ? trim($customfieldValues[$entryId]) : "";
			if($mode == self::MODE_HAS && !strlen($customfieldValue)) continue;
			if($mode == self::MODE_NONE && strlen($customfieldValue)) continue;

			$isPublished = (int)$v["isPublished"];
			if($pub == self::IS_PUBLISH && $isPublished === 0) continue;
			if($pub == self::NO_PUBLISH && $isPublished === 1) continue;
		
			$_arr["entries"][] = array(
				"id" => $entryId,
				"title" => $v["title"],
				"customfield" => $customfieldValue,
				"isPublished" => $isPublished
			);
		}
		
		//JSONを出力
		self::_output($_arr);
	}

	/**
	 * @param array
	 * @return array
	 */
	private function _entryIds(array $arr){
		if(!count($arr)) return array();
	
		$ids = array();
		foreach($arr as $v){
			if(!isset($v["id"]) || !is_numeric($v["id"])) continue;
			$ids[] = (int)$v["id"];
		}
		return $ids;		
	}

	/**
	 * @param string, array
	 * @return array
	 */
	private function _getCustomFieldValues(string $fieldId, array $entryIds){
		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT entry_id, entry_value FROM EntryAttribute ".
				"WHERE entry_id IN (".implode(",", $entryIds).") ".
				"AND entry_field_id = :fieldId",
				array(":fieldId" => $fieldId)
			);
		}catch(Exception $e){
			$res = array();			
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["entry_id"]] = $v["entry_value"];
		}
		
		return $_arr;
	}

	function config_page($message){
		SOY2::import("site_include.plugin.CustomFieldBulkChange.config.CustomFieldBulkChangeConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomFieldBulkChangeConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldBulkChangePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

CustomFieldBulkChangePlugin::register();
