<?php
//SOY2::import("domain.cms.Entry");
EntryCalendarPlugin::register();

class EntryCalendarPlugin{

	const PLUGIN_ID = "entry_calendar";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"記事一覧カレンダー",
			"description"=>"カレンダー形式で記事一覧を表示する。<a href=\"https://saitodev.co/calendar\", target=\"_blank\">出力例</a>",
			"author"=>"齋藤毅",
			"modifier"=>"Tsuyoshi Saito",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array($this,"config"));
		}
	}

	/**
	 * 設定画面表示
	 * @return HTML
	 */
	function config(){
		SOY2::import("site_include.plugin.entry_calendar.config.EntryCalendarConfigPage");
		$form = SOY2HTMLFactory::createInstance("EntryCalendarConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new EntryCalendarPlugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
