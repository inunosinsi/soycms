<?php

class PluginConfigPlugin{

	const PLUGIN_ID = "plugin_config";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインの詳細設定画面のサンプル",
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
				
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this, "config_page"
				));
			}
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.plugin_config.config.PluginConfigSamplePage");
		$form = SOY2HTMLFactory::createInstance("PluginConfigSamplePage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginBasePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginBasePlugin::register();
