<?php

class NoticeArrivalConfigFormPage extends WebPage{
	
	private $configObj;
	
	function NoticeArrivalConfigFormPage(){
		SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Mail"])){
		
			NoticeArrivalUtil::saveMailTitle($_POST["Mail"]["title"]);
			NoticeArrivalUtil::saveMailContent($_POST["Mail"]["content"]);
			
			$this->configObj->redirect("updated");
		}
		
		$this->configObj->redirect("error");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));
		
		$this->addForm("form");
		
		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => NoticeArrivalUtil::getMailTitle()
		));
		
		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => NoticeArrivalUtil::getMailContent()
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>