<?php

class StringBatchReplacePlugin{

	const PLUGIN_ID = "string_batch_replace";
	const DEBUG = 0;	//1を指定すると配列で出力する

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"記事の文字列一括置換プラグイン",
			"description"=>"記事中の文字列を一括で置換します。<br>記事数が多くなったブログで表記の誤りを発見して記事の内容を変更する必要が生じた時にご利用ください。<br>当プラグインを利用する前にデータベースのバックアップを行ってください。",
			"author"=>"saitodev.co",
			"url"=>"https://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//管理側
		if(!defined("_SITE_ROOT_")){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));
		}else{
			CMSPlugin::setEvent("onSite404NotFound", self::PLUGIN_ID, array($this, "onSite404NotFound"));
		}
	}

	function onSite404NotFound(){
		if(!isset($_GET["replace_plugin_mode"]) || !is_numeric($_GET["replace_plugin_mode"]) || (int)$_GET["replace_plugin_mode"] > 2) return;
		if(!isset($_SERVER["PATH_INFO"]) || is_bool(strpos($_SERVER["PATH_INFO"], ".json"))) return;

		preg_match('/_([\d]*?)\.json/', $_SERVER["PATH_INFO"], $tmp);
		if(!isset($tmp[1]) || !is_numeric($tmp[1])) return;

		$entry = soycms_get_entry_object($tmp[1]);
		switch((int)$_GET["replace_plugin_mode"]){
			case 2:	//追記
				$arr = array("content" => (string)$entry->getMore());
				break;
			case 1:	//本文
			default:
				$arr = array("content" => (string)$entry->getContent());
		}
		$arr["title"] = (string)$entry->getTitle();

		self::_output($arr);
	}

	private function _output(array $arr=array()){
		if(self::DEBUG){
			var_dump($arr);
		}else{
			header("HTTP/1.1 200 OK");
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($arr);
		}
		exit;
	}

	/**
	 * 設定画面の表示
	 */
	function config_page(){
		SOY2::import("site_include.plugin.string_batch_replace.config.STRConfigPage");
		$form = SOY2HTMLFactory::createInstance("STRConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new StringBatchReplacePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

StringBatchReplacePlugin::register();
