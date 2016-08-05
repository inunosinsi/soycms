<?php

class IndexPage extends WebPage{
	
	function __construct(){
		WebPage::WebPage();
		
		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		
		$this->createAdd("mail_list", "_common.MailListComponent", array(
			"list" => self::getMailList()
		));
	}
	
	private function getMailList(){
		try{
			return self::mailDao()->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function mailDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_MailDAO");
		return $dao;
	}
}
?>