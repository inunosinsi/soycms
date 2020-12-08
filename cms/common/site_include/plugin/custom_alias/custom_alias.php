<?php

CustomAliasPlugin::register();

class CustomAliasPlugin{

	const PLUGIN_ID = "CustomAlias";
	private $useId;
 	private $prefix;
	private $postfix;
	private $mode;

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
			"version"=>"1.12.1"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				//無駄な処理を避けて、サイトの表示速度の高速化
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

	function onEntryCopy($ids){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$newId = $ids[1];
				$entry = CustomAliasUtil::getEntryById($newId);
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
		$alias = null;
		$entry = &$arg["entry"];
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$alias = self::_generateAlias($entry, $mode);
				break;
			default:
				if(isset($_POST["alias"]) && strlen($_POST["alias"])) $alias = trim($_POST["alias"]);
		}

		if(isset($alias) && strlen($alias)){
			$entry->setAlias($alias);
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$logic->update($entry);
		}
	}

	private function _generateAlias(Entry $entry, $mode, $newId=null){
		switch($mode){
			case CustomAliasUtil::MODE_ID:
				$cnf = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_ID);
				//記事複製時を加味
				$alias = (is_numeric($newId) && $newId > 0) ? $newId : $entry->getId();
				if(isset($cnf["prefix"]) && strlen($cnf["prefix"])){
					$alias = $cnf["prefix"] . $alias;
				}

				if(isset($cnf["postfix"]) && strlen($cnf["postfix"])){
					$alias .= $cnf["postfix"];
				}
			 	return $alias;

			case CustomAliasUtil::MODE_HASH:
				// @ToDo ハッシュ関数を選択できるようにしたい
				return md5($entry->getTitle());
		}
		return null;
	}

	function onCallCustomField(){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				return "";
			default:
				$arg = SOY2PageController::getArguments();
				$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;

				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasFormComponent");
				return CustomAliasFormComponent::buildForm($mode, $entryId);
		}
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
		$page = CustomAliasUtil::getBlogPageById($pageId);
		if(is_null($page->getId())) return "";

		$entryPageUri = CMSUtil::getSiteUrl().$page->getEntryPageURL();
		$entryId = (isset($arg[1]) && is_numeric($arg[1])) ? (int)$arg[1] : null;

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
