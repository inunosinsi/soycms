<?php

class PluginSiteAccessPlugin{

	const PLUGIN_ID = "plugin_site_access";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのサイトアクセスのサンプル",
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
			
				// ページの出力直前の拡張ポイントを利用します。三番目の値の配列のonSiteAccessはプラグインのクラスに追加するメソッド名になります
				CMSPlugin::setEvent("onSiteAccess", self::PLUGIN_ID, array($this, "onSiteAccess"));
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
			
			}
		}
	}

	// CMSPlugin::setEventで追加した拡張ポイントの処理のコードを書きます。
	function onSiteAccess($args){
		// ページコントローラが格納されているが、ページを出力する為の準備は出来ていないので、あまり使えない
		$controller = &$args["controller"];

		// 前に呼び出される別のプラグインで定数の定義をしている可能性があるため、定数を定義する際に必ずdefinedで確認しておくと良い
		if(!defined("SOYCMS_PLUGIN_SAMPLE_MODE")) define("SOYCMS_PLUGIN_SAMPLE_MODE", true);
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginSiteAccessPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginSiteAccessPlugin::register();
