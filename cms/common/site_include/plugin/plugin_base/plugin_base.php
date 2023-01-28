<?php

class PluginBasePlugin{

	const PLUGIN_ID = "plugin_base";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインの基本構造",	//プラグイン名を記載します
			"type" => Plugin::TYPE_NONE,	// /CMSインストールディレクトリ/common/domain/cms/Plugin.class.phpを参考にして種別を指定します
			"description" => "",	//プラグインの説明を記載します
			"author" => "",	//開発者名を記載します
			"url" => "",	//プラグインの説明が記載されているサイトのURLを記載します
			"mail" => "",
			"version" => "1.0"
		));
		
		// 当プラグインが有効であるかを調べる
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				// 公開側ページの方で動作する拡張ポイントで使用したいものを追加する

			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
				
			}
		}
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginBasePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginBasePlugin::register();
