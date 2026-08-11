<?php

class MailInsertTemplateFormPage extends WebPage {

	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.order_mail_insert_template.util.InsertStringTemplateUtil");
	}

	function execute(){
		parent::__construct();

		$cnf = InsertStringTemplateUtil::getConfig();

		DisplayPlugin::toggle("mail_template", count($cnf));

		$this->addSelect("templates", array(
			"name" => "MailTemplate",
			"options" => $cnf,
			"selected" => InsertStringTemplateUtil::getMailFieldIdByItemId($this->itemId)
		));
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
