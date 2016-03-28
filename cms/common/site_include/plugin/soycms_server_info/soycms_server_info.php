<?php
/*
 * SOY CMS サーバー情報表示プラグイン
 *
 */
SOYCMS_Server_Info_Plugin::registerPlugin();

class SOYCMS_Server_Info_Plugin{
	
	const PLUGIN_ID = "soycms_server_info";
	
	function getId(){
		return self::PLUGIN_ID;
	}
	
	/**
	 * 初期化
	 */
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"サーバー情報表示プラグイン",
			"description"=>"SOY CMSのインストールされているサーバーの情報を表示します。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.0"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
	}
	
	/**
	 * 設定画面の表示
	 */
	function config_page($message){
		if(!UserInfoUtil::isDefaultUser())return;
		
		if(isset($_GET["phpinfo"])){
			include(dirname(__FILE__)."/phpinfo.php");
			exit;
		}

		include(dirname(__FILE__)."/config.php");
		$form = SOY2HTMLFactory::createInstance("SOYCMSServerInfoConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		
		//管理側のみ、初期管理者のみ
		if(defined("_SITE_ROOT_") || !class_exists("UserInfoUtil") || !UserInfoUtil::isDefaultUser())return;
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_Server_Info_Plugin();
			
			//この時プラグインを強制的に有効にする
			$filepath = CMSPlugin::getSiteDirectory().'/.plugin/'. self::PLUGIN_ID;
			if(!file_exists($filepath . ".inited") && ini_get("allow_url_fopen")){
				@file_put_contents($filepath .".active","active");
				@file_put_contents($filepath .".inited","inited");
			}
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
	
}

?>