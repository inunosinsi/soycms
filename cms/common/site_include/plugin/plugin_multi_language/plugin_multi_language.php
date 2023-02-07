<?php

class PluginMultiLanguagePlugin{

	const PLUGIN_ID = "plugin_multi_language";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのブログページの多言語化",
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

				// ブログページが読み込まれる直前に
				CMSPlugin::setEvent("onBlogPageLoad", self::PLUGIN_ID, array($this, "onBlogPageLoad"));
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
				
			}
		}
	}

	function onBlogPageLoad($args){
		$page = &$args["page"];
		$webPage = &$args["webPage"];

		// 多言語プラグインで定義している定数がなかった場合はこの場で定義しておく
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");

		// 言語設定によって、ブログページの使用するラベルの設定を切り替える
		switch(SOYCMS_PUBLISH_LANGUAGE){
			case "en":
				$page->setBlogLabelId(4);
				break;
			default:
		}


	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginMultiLanguagePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginMultiLanguagePlugin::register();
