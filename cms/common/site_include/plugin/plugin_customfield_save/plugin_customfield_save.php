<?php

class PluginCustomfieldSavePlugin{

	const PLUGIN_ID = "plugin_customfield_save";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのカスタムフィールドサンプル２",
			"type" => Plugin::TYPE_NONE,
			"description" => "",
			"author" => "",
			"url" => "",
			"mail" => "",
			"version" => "1.0"
		));
		
		// 当プラグインが有効であるかを調べる
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				// 公開側ページの方で動作する拡張ポイントで使用したいものを追加する
			
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
				
				CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));

				CMSPlugin::addCustomFiledFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFiledFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_onBlog"));
			}
		}
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($args){
		$entryId = $args["entry"]->getId();
		
		// 下記の手続きでEntryAttributeテーブルに値を挿入できる
		$attr = soycms_get_entry_attribute_object($entryId, "custom_hoge");
		$attr->setValue($_POST["hoge"]);
		soycms_save_entry_attribute_object($attr);
	}

	// 記事投稿画面用
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildCommonForm($entryId);
	}

	// 各ブログページからの記事投稿画面用
	function onCallCustomField_onBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildCommonForm($entryId);
	}

	/** 
	 * カスタムフィールドの共通箇所
	 * @param int
	 * @return html
	 */
	private function _buildCommonForm(int $entryId){
		SOY2::import("site_include.plugin.plugin_customfield_save.form.PluginCustomfieldSaveSamplePage");
		$form = SOY2HTMLFactory::createInstance("PluginCustomfieldSaveSamplePage");
		$form->setEntryId($entryId);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginCustomfieldSavePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginCustomfieldSavePlugin::register();
