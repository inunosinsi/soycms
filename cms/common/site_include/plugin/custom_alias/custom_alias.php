<?php

use function PHPSTORM_META\map;

CustomAliasPlugin::register();

class CustomAliasPlugin{

	const PLUGIN_ID = "CustomAlias";
	private $useId;
 	private $prefix;
	private $postfix;
	private $mode;

	private $aliases = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"カスタムエイリアス",
			"description"=>"ブログの記事ページのURLの記事毎に変わる部分（エイリアス）を指定できるようにします。<br>SOY CMS 1.2.4以上で動作します。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.14"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}else{
				SOY2::import("site_include.plugin.custom_alias.util.CustomAliasUtil");
				CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryCopy", self::PLUGIN_ID, array($this, "onEntryCopy"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		
		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT id, alias FROM Entry ".
				"WHERE id IN (" . implode(",", $entryIds) . ")"
			);
		}catch(Exception $e){
			$res = array();
		}

		foreach($res as $v){
			$this->aliases[(int)$v["id"]] = $v["alias"];
		}
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addLabel("entry_alias",array(
			"soy2prefix" => "cms",
			"text" => ($entryId > 0) ? self::_getAliasByEntryId($entryId) : ""
		));
	}

	private function _getAliasByEntryId(int $entryId){
		if(isset($this->aliases[$entryId])) return $this->aliases[$entryId];

		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT alias FROM Entry ".
				"WHERE id = :entryId",
				array(":entryId" => $entryId)
			);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["alias"])) ? $res[0]["alias"] : "";
	}

	function onEntryCopy($ids){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$newId = $ids[1];
				$entry = soycms_get_entry_object($newId);
				$alias = self::_generateAlias($entry, $mode, $newId);
				if(strlen($alias)){
					$entry->setAlias($alias);
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					$logic->update($entry);
				}
				break;
			default:
				//何もしない
		}
	}

	function onEntryUpdate($arg){
		$mode = self::_mode();
		$newAlias = null;
		$entry = &$arg["entry"];
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$newAlias = self::_generateAlias($entry, $mode);
				break;
			default:
				if(isset($_POST["alias"]) && strlen($_POST["alias"])) $newAlias = trim($_POST["alias"]);
		}

		if(isset($newAlias) && is_string($newAlias) && strlen($newAlias)){
			if($entry->getAlias() != $newAlias){	// エイリアスが異なっている時のみ記事の更新を行う
				$entry->setAlias($newAlias);
				SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->update($entry);
			}
		}
	}

	private function _generateAlias(Entry $entry, int $mode, int $newId=0){
		switch($mode){
			case CustomAliasUtil::MODE_ID:
				$cnf = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_ID);
				//記事複製時を加味
				$alias = ($newId > 0) ? (string)$newId : (string)$entry->getId();
				if(isset($cnf["prefix"]) && strlen($cnf["prefix"])){
					$alias = $cnf["prefix"] . $alias;
				}

				if(isset($cnf["postfix"]) && strlen($cnf["postfix"])){
					$alias .= $cnf["postfix"];
				}
				return $alias;

			case CustomAliasUtil::MODE_HASH:
				// @ToDo ハッシュ関数を選択できるようにしたい
				if($newId > 0){
					$title = (string)$newId . $entry->getTitle();
				}else{
					$title = (string)$entry->getId() . $entry->getTitle();
				}
				return md5($title);
		}
		return null;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : 0;

		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasHiddenFormComponent");
				return CustomAliasHiddenFormComponent::buildForm($entryId);
			default:
				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasFormComponent");
				return CustomAliasFormComponent::buildForm($mode, $entryId);
		}
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : 0;
		$page = soycms_get_page_object($pageId);
		if(is_null($page->getId())) return "";

		$entryPageUri = CMSUtil::getSiteUrl().$page->getEntryPageURL();
		$entryId = (isset($arg[1]) && is_numeric($arg[1])) ? (int)$arg[1] : 0;

		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasConfirmUrlComponent");
				return CustomAliasConfirmUrlComponent::buildForm($entryId, $entryPageUri);
			default:
				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasFormComponent");
				return CustomAliasFormComponent::buildForm($mode, $entryId, $entryPageUri);
		}
	}

	//互換性を持たせる為のヘルパー
	private function _mode(){
		if(is_null($this->mode)){
			if($this->useId) return CustomAliasUtil::MODE_ID;
			return CustomAliasUtil::MODE_MANUAL;
		}
		return $this->mode;
	}

	function config_page($message){
		SOY2::import("site_include.plugin.custom_alias.config.CustomAliasPluginFormPage");
		$form = SOY2HTMLFactory::createInstance("CustomAliasPluginFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getUseId(){
		return $this->useId;
	}
	function setUseId($useId){
		$this->useId = $useId;
	}

	function getPrefix(){
		return $this->prefix;
	}
	function setPrefix($prefix){
		$this->prefix = $prefix;
	}

	function getPostfix(){
		return $this->postfix;
	}
	function setPostfix($postfix){
		$this->postfix = $postfix;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomAliasPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj, "init"));
	}
}
