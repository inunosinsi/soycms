<?php

class AutoRankingConfigFormPage extends WebPage{
	
	private $configObj;
	
	function AutoRankingConfigFormPage(){
		SOY2::import("module.plugins.common_auto_ranking.util.AutoRankingUtil");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			AutoRankingUtil::setConfig($_POST["Config"]);
		}
		
		$this->configObj->redirect("updated");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$config = AutoRankingUtil::getConfig();
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$this->addInput("count", array(
			"name" => "Config[count]",
			"value" => (isset($config["count"])) ? (int)$config["count"] : "",
			"style" => "ime-mode:inactive;width:30px;"
		));
		
		$this->addInput("period", array(
			"name" => "Config[period]",
			"value" => (isset($config["period"])) ? (int)$config["period"] : "",
			"style" => "ime-mode:inactive;width:30px;"
		));
		
		$this->addLabel("job_path", array(
			"text" => $this->buildPath(). " " . SOYSHOP_ID
		));
		
		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));
	}
	
	function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>