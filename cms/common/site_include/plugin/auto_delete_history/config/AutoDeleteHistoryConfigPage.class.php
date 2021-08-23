<?php

class AutoDeleteHistoryConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.auto_delete_history.util.AutoDeleteHistoryUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			$cnf = $_POST["Config"]["entry"];
			$isActive = (isset($cnf["active"])) ? (int)$cnf["active"] : AutoDeleteHistoryUtil::INACTIVE;
			$this->pluginObj->setIsEntryDelete($isActive);
			$this->pluginObj->setEntryCdate((int)$cnf["cdate"]);
			$this->pluginObj->setEntryCount((int)$cnf["count"]);

			$cnf = $_POST["Config"]["template"];
			$isActive = (isset($cnf["active"])) ? (int)$cnf["active"] : AutoDeleteHistoryUtil::INACTIVE;
			$this->pluginObj->setIsTemplateDelete($isActive);
			$this->pluginObj->setTemplateCdate((int)$cnf["cdate"]);
			$this->pluginObj->setTemplateCount((int)$cnf["count"]);

			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("active_auto_delete_entry", array(
			"name" => "Config[entry][active]",
			"value" => AutoDeleteHistoryUtil::ACTIVE,
			"selected" => ($this->pluginObj->getIsEntryDelete() == AutoDeleteHistoryUtil::ACTIVE),
			"label" => "記事の編集履歴の自動削除を有効にする"
		));

		$this->addInput("entry_cdate", array(
			"name" => "Config[entry][cdate]",
			"value" => $this->pluginObj->getEntryCdate(),
			"style" => "width:120px"
		));

		$this->addInput("entry_count", array(
			"name" => "Config[entry][count]",
			"value" => $this->pluginObj->getEntryCount(),
			"style" => "width:80px"
		));

		$this->addCheckBox("active_auto_delete_template", array(
			"name" => "Config[template][active]",
			"value" => AutoDeleteHistoryUtil::ACTIVE,
			"selected" => ($this->pluginObj->getIsTemplateDelete() == AutoDeleteHistoryUtil::ACTIVE),
			"label" => "テンプレートの変更履歴の自動削除を有効にする"
		));

		$this->addInput("template_cdate", array(
			"name" => "Config[template][cdate]",
			"value" => $this->pluginObj->getTemplateCdate(),
			"style" => "width:120px"
		));

		$this->addInput("template_count", array(
			"name" => "Config[template][count]",
			"value" => $this->pluginObj->getTemplateCount(),
			"style" => "width:80px"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
