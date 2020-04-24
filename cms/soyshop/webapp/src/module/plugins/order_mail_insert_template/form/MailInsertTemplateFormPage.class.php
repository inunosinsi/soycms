<?php

class MailInsertTemplateFormPage extends WebPage {

	private $config;
	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.order_mail_insert_template.util.InsertStringTemplateUtil");
	}

	function execute(){
		parent::__construct();

		$config = InsertStringTemplateUtil::getConfig();

		DisplayPlugin::toggle("mail_template", count($config));

		$this->addSelect("templates", array(
			"name" => "MailTemplate",
			"options" => $config,
			"selected" => InsertStringTemplateUtil::getMailFieldIdByItemId($this->itemId)
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
