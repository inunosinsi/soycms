<?php
class CommonMailbuilderConfigFormPage extends WebPage{

	function CommonMailbuilderConfigFormPage() {
		SOY2::import("module.plugins.common_mailbuilder.common.CommonMailbuilderCommon");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["content"])){
						
			foreach($_POST["content"] as $key => $content){
				CommonMailbuilderCommon::saveMailContent($content, $key);
			}
			
			SOY2PageController::jump("Config.Detail?plugin=common_mailbuilder&updated");
		}
		
		SOY2PageController::jump("Config.Detail?plugin=common_mailbuilder&failed");
	}
	
	function execute(){
		
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addTextArea("mail_user", array(
			"name" => "content[user]",
			"value" => CommonMailbuilderCommon::getMailContent("user")
		));
		
		$this->addTextArea("mail_admin", array(
			"name" => "content[admin]",
			"value" => CommonMailbuilderCommon::getMailContent("admin")
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>