<?php
class URLShortenerPluginInitFormPage extends WebPage{
	
	private $pluginObj;
	private $version;
	
	function __construct(){}
	
	function doPost(){
    	if(soy2_check_token() && isset($_POST["init"])){
			self::_init();
    	}
    	CMSPlugin::redirectConfigPage();
	}
	
	function execute(){
		parent::__construct();
		$this->addForm("url_shortener_form");
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config_form.html";
	}
	
	/**
	 * 初期化処理
	 */
	function _init(){
		//table
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery(self::_sql());
		}catch(Exception $e){
			return false;//失敗
		}
		//version
		$this->pluginObj->setVersion(UrlShortenerPlugin::getLatestVersion());
		CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
		
		return true;
	}
	
	/**
	 * @return String sql for init
	 */
	private function _sql(){
		return file_get_contents(dirname(dirname(dirname(__FILE__)))."/sql/init_".SOYCMS_DB_TYPE.".sql");
	}
}
