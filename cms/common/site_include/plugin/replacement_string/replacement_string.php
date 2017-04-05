<?php
ReplacementStringPlugin::register();

class ReplacementStringPlugin{
	
	const PLUGIN_ID = "replacement_string"; 
	
	private $stringList = array();
	
	function getId(){
		return self::PLUGIN_ID;	
	}
	
	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"置換文字列生成プラグイン",
			"description"=>"各ページで使用できる置換文字列を追加します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.2"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"	
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID) && defined("_SITE_ROOT_")){
			CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this, "onOutput"));
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
		
		if(!count($this->stringList)) return $html;
		
		foreach($this->stringList as $v){
			$html = str_replace($v["symbol"], $v["string"], $html);
		}
		
		return $html;
	}

	function config_page(){

		SOY2::import("site_include.plugin.replacement_string.config.ReplacementStringConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReplacementStringConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	function getStringList(){
		return $this->stringList;
	}
	function setStringList($stringList){
		$this->stringList = $stringList;
	}
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new ReplacementStringPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>