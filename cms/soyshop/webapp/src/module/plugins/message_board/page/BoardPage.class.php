<?php

class BoardPage extends WebPage{
	
	private $configObj;
	private $mesDao;
	
	function __construct(){
		SOY2::import("module.plugins.message_board.component.MessageListComponent");
		SOY2::imports("module.plugins.message_board.domain.*");
		$this->mesDao = SOY2DAOFactory::create("SOYShop_MessageBoardDAO");
	}
	
	function doPost(){
		if(soy2_check_token() && isset($_POST["Message"])){
			
			$obj = SOY2::cast("SOYShop_MessageBoard", $_POST["Message"]);
			
			$session = SOY2ActionSession::getUserSession();
			$accountId = $session->getAttribute("userid");
			$obj->setAccountId($accountId);
			
			try{
				$this->mesDao->insert($obj);
			}catch(Exception $e){
				var_dump($e);
			}
			
			//みんなにメールを送信する
			$list = self::getAccountList();
			if(count($list)){
				$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
				foreach($list as $accId => $acc){
					//自身の場合は送信しない
					if((int)$accId === $obj->getAccountId()) continue;
					
					if(!isset($acc["mailaddress"]) || !strlen($acc["mailaddress"])) continue;
					$title = "[SOY Shop]連絡掲示板に新しい連絡がありました。";
					$body = "新しいメッセージ:\n\n" . $obj->getMessage();
					$mailLogic->sendMail($acc["mailaddress"], $title, $body);
				}
			}
			
			SOY2PageController::jump("Extension.message_board?post");
		}
	}
	
	function execute(){
				
		WebPage::__construct();
		
		$this->createAdd("message_list", "MessageListComponent", array(
			"list" => self::get(),
			"accountList" => self::getAccountList()
		));
		
		$this->addForm("form");
		
		$this->addTextArea("message_textarea", array(
			"name" => "Message[message]",
			"value" => ""
		));
	}
	
	private function get(){
		$this->mesDao->setLimit(30);
		
		try{
			return $this->mesDao->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function getAccountList(){
		$old = SOYAppUtil::switchAdminDsn();
		try{
			$admins = SOY2DAOFactory::create("admin.AdministratorDAO")->get();
		}catch(Exception $e){
			$admins = array();
		}
		SOYAppUtil::resetAdminDsn($old);
		
		if(!count($admins)) return array();
		
		$list = array();
		foreach($admins as $admin){
			$vals = array();
			$vals["name"] = (!is_null($admin->getName())) ? $admin->getName() : $admin->getUserId();
			$vals["mailaddress"] = $admin->getEmail();
			
			$list[$admin->getId()] = $vals;
		}
		
		return $list;
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>