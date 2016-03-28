<?php
class URLShortenerPluginInitFormPage extends WebPage{
	
	private $pluginObj;
	private $version;
	
	function URLShortenerPluginInitFormPage(){}
	
	function doPost(){
    	if(soy2_check_token() && isset($_POST["init"])){
			$this->initURLShortener();
    	}
    	CMSPlugin::redirectConfigPage();
	}
	
	function execute(){
		WebPage::WebPage();

		$this->addForm("url_shortener_form",array());

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
	function initURLShortener(){
		//table
		$sql = self::getSQL();
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery($sql);
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
	static function getSQL(){
		$sql = file_get_contents(dirname(dirname(dirname(__FILE__)))."/sql/init_".SOYCMS_DB_TYPE.".sql");
		return $sql;
	}

}

?>