<?php
FacebookGraphAPIPlugin::registerPlugin();

class FacebookGraphAPIPlugin {

	const PLUGIN_ID = "facebook_graph_api";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "FacebookグラフAPIプラグイン",
			"type" => Plugin::TYPE_EXTERNAL,
			"description"=> "",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
		}
	}

	function config_page($message){
		SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config.FbGraphConfigPage");
		$form = SOY2HTMLFactory::createInstance("FbGraphConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new FacebookGraphAPIPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
