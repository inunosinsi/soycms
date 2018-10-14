<?php

BlogDescriptionEditorPlugin::register();

class BlogDescriptionEditorPlugin{

	const PLUGIN_ID = "blog_description_editor";

	private $isWYSIWYG = false;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ブログ説明WYSIWIGプラグイン",
			"description"=>"ブログの設定にある説明でWYSIWIGエディタを使用できるようにする",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onBlogSetupWYSIWYG",$this->getId(),array($this,"onSetupWYSIWYG"));
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.soycms_blog_description_editor.config.BlogEditorConfigPage");
		$form = SOY2HTMLFactory::createInstance("BlogEditorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onSetupWYSIWYG(){
		$_COOKIE["blog_text_editor"] = ($this->getIsWYSIWYG()) ? "tinyMCE" : "plain";
	}

	function getIsWYSIWYG(){
		return $this->isWYSIWYG;
	}
	function setIsWYSIWYG($isWYSIWYG){
		$this->isWYSIWYG = $isWYSIWYG;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new BlogDescriptionEditorPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
