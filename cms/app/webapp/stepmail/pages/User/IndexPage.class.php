<?php

class IndexPage extends WebPage{
	
	function __construct(){
		WebPage::WebPage();
		
		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("canceled", isset($_GET["canceled"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		
		$this->createAdd("user_list", "_common.UserListComponent", array(
			"list" => self::getNotSendUserList()
		));
	}
	
	private function getNotSendUserList(){
		try{
			return self::sendDao()->getNotSendUser();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function sendDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_NextSendDAO");
		return $dao;
	}
}
?>