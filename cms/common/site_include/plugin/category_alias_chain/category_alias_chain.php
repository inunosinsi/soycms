<?php
CategoryAliasChainPlugin::registerPlugin();

class CategoryAliasChainPlugin{

	const PLUGIN_ID = "category_alias_chain";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "カテゴリエイリアスチェインプラグイン",
			"description"=> "記事に紐付いているカテゴリのエイリアスが複数の場合、エイリアスを半角スペースで繋げて出力するタグを追加する",
			"author"=> "齋藤毅",
			"url" => "https://saitodev.co/app/bulletin/board/topic/detail/36",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));

			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}
		}
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entry = $arg["entry"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addLabel("category_alias_chain", array(
			"text" => (is_array($entry->getLabels()) && count($entry->getLabels())) ? self::_buildAliasChain($entry->getLabels()) : "",
			"soy2prefix" => "cms"
		));
	}

	private function _buildAliasChain(array $labels){
		if(!count($labels)) return "";

		$chain = "";
		foreach($labels as $label){
			$chain .= $label->getAlias() . " ";
		}

		return trim($chain);
	}

	function config_page($message){
		$txt = "下記タグの用途はプラグインの情報のURL先のページをご確認ください。";
		$txt .= "<pre>cms:id=\"category_alias_chain\"</pre>";
		return $txt;
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CategoryAliasChainPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
