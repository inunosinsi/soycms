<?php
/*
 * Created on 2009/06/12
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

RecordDeadLinkPlugin::register();

class RecordDeadLinkPlugin{
	
	const PLUGIN_ID = "record_dead_link"; 
	
	function getId(){
		return self::PLUGIN_ID;	
	}
	
	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"リンク切れページURL記録プラグイン",
			"description"=>"リンク切れページを開いた時の参照元を記録するプラグイン",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com",
			"mail"=>"info@n-i-agroinformatics.com",
			"version"=>"0.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"	
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onSite404NotFound", $this->getId(), array($this, "execute"));
		}else{
			CMSPlugin::setEvent("onActive", $this->getId(), array($this, "createTable"));
		}	
	}
	
	function execute(){
		if(isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], $_SERVER["HTTP_HOST"]) == false){
			SOY2::imports("site_include.plugin.". self::PLUGIN_ID . ".domain.*");
			$dao = SOY2DAOFactory::create("RecordDeadLinkDAO");
			
			$obj = new RecordDeadLink();
			$obj->setReferer($_SERVER["HTTP_REFERER"]);
			$obj->setUrl($_SERVER["REQUEST_URI"]);
			
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}
	
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM RecordDeadLink", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYCMS_DB_TYPE . ".sql");
		$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("create" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				var_dump($e);
				//
			}
		}

		return;
	}

	function config_page(){

		include_once(dirname(__FILE__) . "/config/RecordDeadLinkConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("RecordDeadLinkConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new RecordDeadLinkPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>