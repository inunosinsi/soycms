<?php

class B2ExportFormPage extends WebPage {

	function __construct(){

	}

	function execute(){
		parent::__construct();

		$this->addCheckBox("change_client_claimant", array(
			"name" => "change_client_claimant",
			"value" => 1,
			"label" => "依頼主の内容を各注文の請求者の情報に変更する"
		));
	}
}
