<?php

class SalePeriodOptionConfigFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.common_sale_period.util.SalePeriodUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			
			if(isset($_POST["Config"])){
				SalePeriodUtil::saveConfig($_POST["Config"]);
			}
			
			if(isset($_POST["Mail"])){
				foreach(SalePeriodUtil::getMailTypes() as $type){
					SalePeriodUtil::saveMailConfig($_POST["Mail"][$type], $type);
				}
			}
			
			$this->configObj->redirect("updated");	
		}
	}
	
	function execute(){
		parent::__construct();
		
		$config = SalePeriodUtil::getConfig();
		
		
		
		$this->addForm("form");
		
		$this->addInput("sale_end_date", array(
			"name" => "Config[end]",
			"value" => (int)$config["end"],
			"style" => "width: 50px;text-align:right;"
		));
		
		$this->addLabel("job_path", array(
			"text" => self::buildPath(). " " . SOYSHOP_ID
		));
		
		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));
		
		foreach(SalePeriodUtil::getMailTypes() as $type){
			$mail = SalePeriodUtil::getMailConfig($type);
			$this->addInput("mail_" . $type . "_title", array(
				"name" => "Mail[" . $type . "][title]",
				"value" => $mail["title"]
			));
			
			$this->addTextArea("mail_" . $type . "_content", array(
				"name" => "Mail[" . $type . "][content]",
				"value" => $mail["content"]
			));
		}
			
	}
	
	private function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}