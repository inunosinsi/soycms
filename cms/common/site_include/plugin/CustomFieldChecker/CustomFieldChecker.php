<?php
class CustomFieldCheckerPlugin{

	const PLUGIN_ID = "CustomFieldChecker";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"カスタムフィールドチェッカー",
			"description"=>"カスタムフィールドの使用状況を調べます",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"saito@saitodev.co",
			"version"=>"1.0.0"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));
		}
	}

	function config_page($message){
		SOY2::import("site_include.plugin.CustomFieldChecker.config.CustomFieldCheckerConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomFieldCheckerConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldCheckerPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

CustomFieldCheckerPlugin::register();
