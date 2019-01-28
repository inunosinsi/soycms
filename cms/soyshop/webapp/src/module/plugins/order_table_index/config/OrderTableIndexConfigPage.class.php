<?php

class OrderTableIndexConfigPage extends WebPage {

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->addLabel("cron_path", array(
			"text" => "php " . SOY2::RootDir() . "module/plugins/order_table_index/job/optimize.php " . SOYSHOP_ID . " 1000"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
