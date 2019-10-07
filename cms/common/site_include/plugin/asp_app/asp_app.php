<?php
AspAppPlugin::register();

class AspAppPlugin{

	const PLUGIN_ID = "AspAppPlugin";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "SOY App ASP版運営プラグイン",
			"description" => "任意のSOY AppをASPモードにする",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.2"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));
			//公開側
			}else{
				//cms:module="asp.user_app_register"
			}

        //プラグインの初回動作
		}else{
			CMSPlugin::setEvent('onActive', $this->getId(), array($this, "createTable"));
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.asp_app.config.AspAppConfigPage");
		$page = SOY2HTMLFactory::createInstance("AspAppConfigPage");
		$page->setPluginObj($this);
		$page->execute();
		return $page->getObject();
	}

	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM asp_app_pre_register", array());
			return;//テーブル作成済み
		}catch(Exception $e){
		}

		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");

		try{
			$dao->executeUpdateQuery($sql, array());
		}catch(Exception $e){
			//
		}

		return;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new AspAppPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
