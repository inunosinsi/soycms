<?php

class ReadEntryCountConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Config"])){
				$this->pluginObj->setLimit((int)$_POST["Config"]["limit"]);
				$moduleOnlyMode = (isset($_POST["Config"]["moduleOnlyMode"])) ? (int)$_POST["Config"]["moduleOnlyMode"] : 0;
				$this->pluginObj->setModuleOnlyMode($moduleOnlyMode);
				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["reset"])){
				$dao = new SOY2DAO();
				try{
					$dao->executeUpdateQuery("UPDATE ReadEntryCount SET count = 0;");
				}catch(Exception $e){
					//var_dump($e);
				}
				//SOY2::imports("site_include.plugin.read_entry_count.domain.*");
				//SOY2DAOFactory::create("ReadEntryCountDAO")->reset();
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		parent::__construct();
		
		$isBlockPluginVer = is_numeric(strpos($this->pluginObj->getId(), "BlockPlugin"));
		DisplayPlugin::toggle("block_plugin_ver", $isBlockPluginVer);
		DisplayPlugin::toggle("normal_ver", !$isBlockPluginVer);

		$this->addForm("form");

		$this->addInput("limit", array(
			"name" => "Config[limit]",
			"value" => $this->pluginObj->getLimit()
		));

		DisplayPlugin::toggle("module_only_mode_item", !$isBlockPluginVer);

		$this->addCheckBox("module_only_mode", array(
			"name" => "Config[moduleOnlyMode]",
			"value" => 1,
			"selected" => ((int)$this->pluginObj->getModuleOnlyMode() === 1),
			"label" => "公開側で使用するタグはモジュール版のみ使用する"
		));

		$this->addForm("reset_form");
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
