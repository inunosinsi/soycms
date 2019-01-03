<?php

CustomSearchFieldEntryListBlockPlugin::register();

class CustomSearchFieldEntryListBlockPlugin{

	const PLUGIN_ID = "CustomSearchFieldEntryListBlock";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID,array(
			"name" => "カスタムサーチフィールド記事一覧ブロックプラグイン",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::addPluginConfigPage(CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID, array(
					$this,"config_page"
				));

			//公開側
			}else{
				//CMSPlugin::setEvent('onEntryOutput', CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID, array($this, "display"));
			}

			CMSPlugin::setEvent('onPluginBlockLoad',CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID, array($this, "returnPluginId"));

		}
	}

	/**
	 * onEntryOutput
	 */
	function display($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.CustomSearchFieldEntryListBlock.config.CustomSearchFieldEntryListConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomSearchFieldEntryListConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onLoad(){
		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

		$uri = $_SERVER["SOYCMS_PAGE_URI"];
		$pathinfo = $_SERVER["PATH_INFO"];

		if(strlen($uri)){
			$args = explode("/", trim(str_replace($uri . "/", "", $pathinfo), "/"));
		}else{
			$args = explode("/", trim($pathinfo, "/"));
		}

		if(!isset($args[0]) || !isset($args[1])) return array();
		$key = trim($args[0]);
		$v = trim($args[1]);

		//ラベルIDを取得とデータベースから記事の取得件数指定
		// $labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		// if(is_null($labelId)) return array();

		$count = PluginBlockUtil::getLimitByPageId($pageId);
		return SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.SearchLogic")->getEntryList($key, $v, 0, 0, $count);
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new CustomSearchFieldEntryListBlockPlugin();
		}

		CMSPlugin::addPlugin(CustomSearchFieldEntryListBlockPlugin::PLUGIN_ID, array($obj, "init"));
	}
}
