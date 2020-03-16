<?php
DisplayRestrictionsPlugin::register();

class DisplayRestrictionsPlugin{

	const PLUGIN_ID = "display_restrictions";

	//表示制限をかけるページ
	//Array<ページID => 0 | 1> 挿入しないページが1
	var $config_per_page = array();

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ページ毎表示制限プラグイン",
			"description"=>"ページ毎に表示の制限を行うことが出来ます。",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3046",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config"));

			//CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));

			//activeな時だけロード
			CMSPlugin::setEvent('onPageLoad', self::PLUGIN_ID, array($this,"onPageLoad"), array("filter" => "all"));
		}
	}

	function onPageLoad($args){
		$pageId = $_SERVER["SOYCMS_PAGE_ID"];
		//該当するページであれば現在ログインしているか調べてからエラーを投げてみる
		if(isset($this->config_per_page[$pageId])){
			//セッションからログインしているかどうか取得
			SOY2::import("util.UserInfoUtil");
			SOY2::import("domain.admin.Site");

			if(!UserInfoUtil::isLoggined()){
				throw new Exception();
			}
		}
	}

	/**
	 * 設定画面表示
	 * @return HTML
	 */
	function config(){
		SOY2::import("site_include.plugin.display_restrictions.config.DisplayRestrictionsConfigPage");
		$form = SOY2HTMLFactory::createInstance("DisplayRestrictionsConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new DisplayRestrictionsPlugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
