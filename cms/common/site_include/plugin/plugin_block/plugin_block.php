<?php

class PluginBlockPlugin{

	const PLUGIN_ID = "plugin_block";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのプラグインブロック",
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

				// プラグインブロックの挙動用のイベントを追加
				CMSPlugin::setEvent("onPluginBlockLoad", self::PLUGIN_ID, array($this, "onPluginBlockLoad"));
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
			
				// プラグインブロックの設定画面で当プラグインを項目に追加
				CMSPlugin::setEvent("onPluginBlockAdminReturnPluginId", self::PLUGIN_ID, array($this, "returnPluginId"));
			}
		}
	}

	function onPluginBlockLoad(){
		// プラグインブロック内で出力したい記事一覧を取得する
		$pdh = new PDO(_SITE_DSN_, _SITE_DB_USER_, _SITE_DB_PASSWORD_);
	
		// 新着記事5件を降順で取得
		$sql = "SELECT * FROM Entry WHERE isPublished = 1 ORDER BY cdate DESC LIMIT 5";
		$stmt = $pdh->prepare($sql);
		$stmt->execute();
		$entries = $stmt->fetchAll();

		return $entries;
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginBlockPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginBlockPlugin::register();
