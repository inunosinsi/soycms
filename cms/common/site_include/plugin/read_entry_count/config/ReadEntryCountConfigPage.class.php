<?php

class ReadEntryCountConfigPage extends WebPage{

  private $pluginObj;

  function __construct(){}

  function doPost(){
    if(soy2_check_token()){
      $this->pluginObj->setLimit((int)$_POST["Config"]["limit"]);
      CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
    }
  }

  function execute(){
    if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			WebPage::__construct();
		}

    $this->addForm("form");

    $this->addInput("limit", array(
      "name" => "Config[limit]",
      "value" => $this->pluginObj->getLimit()
    ));
  }

  function setPluginObj($pluginObj){
    $this->pluginObj = $pluginObj;
  }
}
