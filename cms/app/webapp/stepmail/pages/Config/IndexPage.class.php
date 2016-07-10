<?php

class IndexPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Mail"])){
			StepMail_DataSets::put("mail_footer", $_POST["Mail"]["footer"]);
			CMSApplication::jump("Config?updated");
		}
		
		CMSApplication::jump("Config?failed");
	}
	
	function IndexPage(){
		SOY2::import("domain.StepMail_DataSets");
		
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addTextArea("mail_footer", array(
			"name" => "Mail[footer]",
			"value" => StepMail_DataSets::get("mail_footer", "")
		));
		
		$this->addLabel("execute", array(
			"text" => "php " . str_replace("\\", "/", STEPMAIL_SRC) . "job/exe.php " . STEPMAIL_SHOP_ID
		));
		
		$this->addLabel("shop_id", array(
			"text" => STEPMAIL_SHOP_ID
		));
	}
}
?>