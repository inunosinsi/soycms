<?php
InvalidNotFoundPlugin::registerPlugin();

class InvalidNotFoundPlugin {

	const PLUGIN_ID = "invalid_not_found";

	//挿入するページ
	var $config_per_page = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "404NotFound無効ページ設定プラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=> "404NotFoundを出力しないページを設定します",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onAfterGettingPageObject', self::PLUGIN_ID, array($this, "onAfterGettingPageObject"));
			}
		}
	}

	function onAfterGettingPageObject($arg){
		if(defined("INVALID_404_NOT_FOUND")) return;
		
		$pageId = (int)$arg["pageId"];
		define("INVALID_404_NOT_FOUND", (is_array($this->config_per_page) && isset($this->config_per_page[$pageId])));
	}

	function config_page($message){
		SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config.NotFoundConfigPage");
		$form = SOY2HTMLFactory::createInstance("NotFoundConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new InvalidNotFoundPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
