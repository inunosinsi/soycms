<?php
AlertBrowserClosePlugin::register();

class AlertBrowserClosePlugin{

	const PLUGIN_ID = "AlertBrowserClosePlugin";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ブラウザを閉じる前にアラートプラグイン",
			"description"=>"記事編集中にブラウザを閉じようとした時、アラートを出す",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){
			
			//管理側
			if(!defined("_SITE_ROOT_")){
			
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onEntryEditor"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onEntryEditor"));
			//公開側	
			}else{
				
			}
			
        //プラグインの初回動作
		}else{
			//
		}
	}
	
	function onEntryEditor(){
		return "<script>" . file_get_contents(dirname(__FILE__) . "/js/alert.js") . "</script>";
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new AlertBrowserClosePlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>