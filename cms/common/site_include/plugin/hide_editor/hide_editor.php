<?php
HideEditorPlugin::register();

class HideEditorPlugin{

	const PLUGIN_ID = "hide_editor";
	
	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"エディタ非表示プラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=>"記事投稿画面で本文と追記の入力欄を非表示にします",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//公開側
			if(defined("_SITE_ROOT_")){
				// 何もしない
			}else{
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onEntryScript', self::PLUGIN_ID, array($this, "onEntryScript"));
			}
		}
	}

	function onEntryScript(){
		$script[] = "<script>";
		$script[] = file_get_contents(__DIR__."/js/entry.js");
		$script[] = "</script>";
		return implode("\n", $script);
	}

	function config_page(){
		return "記事投稿画面で本文と追記の入力欄を非表示にします";
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new HideEditorPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
