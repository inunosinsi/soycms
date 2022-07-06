<?php
AutoRemoveCachePlugin ::register();
class AutoRemoveCachePlugin {

	const PLUGIN_ID = "auto_remove_cache";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "キャッシュ自動削除プラグイン",
			"description" => "キャッシュの削除のjobをcronに登録することで定期的にキャッシュを自動削除します",
			"author" => "齋藤毅",
			"url" => "http://saitodev.co/",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//設定画面
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this, "config_page"
			));
		}
	}

	function config_page(){
        SOY2::import("site_include.plugin.auto_remove_cache.config.RemoveCacheConfigPage");
		$form = SOY2HTMLFactory::createInstance("RemoveCacheConfigPage");
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new AutoRemoveCachePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
