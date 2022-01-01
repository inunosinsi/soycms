<?php

class EntryInfoConfigPage extends WebPage {

    private $pluginObj;

    function __construct(){}

    function doPost(){
        if(soy2_check_token()){
            $on = (isset($_POST["mode"]) && is_numeric($_POST["mode"])) ? (int)$_POST["mode"] : EntryInfoUtil::MODE_NONE;
            $this->pluginObj->setMode($on);
            CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
            CMSPlugin::redirectConfigPage();
        }
    }

    function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("mode", array(
			"name" => "mode",
			"value" => EntryInfoUtil::MODE_REACQUIRE,
			"selected" => ((int)$this->pluginObj->getMode() == EntryInfoUtil::MODE_REACQUIRE),
			"label" => "記事投稿時にメタ情報の記述がない場合はトップページのメタ情報を取得して出力する"
		));
    }

    function setPluginObj($pluginObj){
        $this->pluginObj = $pluginObj;
    }
}