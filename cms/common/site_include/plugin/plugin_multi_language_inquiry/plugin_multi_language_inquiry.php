<?php

class PluginMultiLanguageInquiryPlugin{

	const PLUGIN_ID = "plugin_multi_language_inquiry";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのお問い合わせページの多言語化",
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

				CMSPlugin::setEvent("onApplicationPageLoad", self::PLUGIN_ID, array($this, "onApplicationPageLoad"));
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
				
			}
		}
	}

	function onApplicationPageLoad($args){
		$page = &$args["page"];
		$webPage = &$args["webPage"];

		// 多言語プラグインで定義している定数がなかった場合はこの場で定義しておく
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");

		// 日本語ページの場合は何もしない
		if(SOYCMS_PUBLISH_LANGUAGE == "jp") return;

		$lines = explode("\n", $page->getTemplate());
		$n = count($lines);
		for($i = 0; $i < $n; $i++){
			$line = $lines[$i];

			// コメントがある行以外はスルー
			if(!strlen(trim($line)) || is_bool(strpos($line, "<!--"))) continue;

			preg_match('/<!--.*app:id=\"soyinquiry\".*app:formid=\"(.*?)\".*-->/', $line, $tmp);
			if(!count($tmp)) continue;

			$appId = $tmp[1]."_en";
			$lines[$i] = str_replace($tmp[1], $appId, $line);
		}
		
		$page->setTemplate(implode("\n", $lines));
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginMultiLanguageInquiryPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginMultiLanguageInquiryPlugin::register();
