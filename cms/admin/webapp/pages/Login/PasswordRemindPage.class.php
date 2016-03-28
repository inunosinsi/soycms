<?php
class PasswordRemindPage extends WebPage{

	function doPost(){
		if(soy2_check_token()){
			$flashSession = SOY2ActionSession::getFlashSession();
			$flashSession->clearAttributes();
			$flashSession->resetFlashCounter();
			
			$result = SOY2ActionFactory::createInstance("SendPasswordRemindMailAction")->run();
			if($result->success()){
				$flashSession->setAttribute("isSended", true);
			}else{
				$flashSession->setAttribute("errorMessage", $result->getErrorMessage("error"));
			}

			SOY2PageController::jump("PasswordRemind");
		}
	}
	
	function PasswordRemindPage() {
		WebPage::WebPage();

		$isSended = SOY2ActionSession::getFlashSession()->getAttribute("isSended");
		$errorMessage = SOY2ActionSession::getFlashSession()->getAttribute("errorMessage");
		
		HTMLHead::addLink("style", array(
				"rel" => "stylesheet",
				"type" => "text/css",
				"href" => SOY2PageController::createRelativeLink("./css/login/style.css") . "?" . SOYCMS_BUILD_TIME
		));
		
		$this->createAdd("head", "HTMLHead", array(
			"title" => "SOY CMS Password Reminder",
		));

		$this->addImage("biglogo", array(
			"src" => SOY2PageController::createRelativeLink("css/img/logo_big.gif"),
		));
		
		$this->addModel("sendmail", array(
			"visible" => !$isSended,
		));

		$this->addModel("sended", array(
			"visible" => $isSended,
		));

		$this->addForm("remind_form");

		$this->addLabel("message", array(
			"visible" => !$isSended,
			"text" => $errorMessage,
		));
	}
}
?>