<?php

class ReadEntryCountConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Config"])){
				$this->pluginObj->setLimit((int)$_POST["Config"]["limit"]);
				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["reset"])){
				$dao = new SOY2DAO();
				try{
					$dao->executeUpdateQuery("UPDATE ReadEntryCount SET count = 0;");
				}catch(Exception $e){
					var_dump($e);
				}
				//SOY2::imports("site_include.plugin.read_entry_count.domain.*");
				//SOY2DAOFactory::create("ReadEntryCountDAO")->reset();
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			parent::__construct();
		}

		$this->addForm("form");

		$this->addInput("limit", array(
			"name" => "Config[limit]",
			"value" => $this->pluginObj->getLimit()
		));

		$this->addForm("reset_form");
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
