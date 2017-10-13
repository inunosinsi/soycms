<?php
ConvertImageUrlPlugin::registerPlugin();

class ConvertImageUrlPlugin{

	const PLUGIN_ID = "convert_image_url";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "画像URL変換プラグイン",
			"description"=> "SSL対応時にブログで投稿した画像のパスがhttpが始まるため、/からはじまる絶対パスに変換する",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.2"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
		}
	}

	function config_page($message){
		SOY2::import("site_include.plugin.convert_image_url.config.ConvertImageUrlConfigPage");
		$form = SOY2HTMLFactory::createInstance("ConvertImageUrlConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new ConvertImageUrlPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
