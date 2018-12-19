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

	function __construct(){
		parent::__construct();

		//nullでは返さない
		$isSended = (!is_null(SOY2ActionSession::getFlashSession()->getAttribute("isSended")));
		$errorMessage = SOY2ActionSession::getFlashSession()->getAttribute("errorMessage");

		// HTMLHead::addLink("style", array(
		// 		"rel" => "stylesheet",
		// 		"type" => "text/css",
		// 		"href" => SOY2PageController::createRelativeLink("./css/login/style.css") . "?" . SOYCMS_BUILD_TIME
		// ));

		$this->createAdd("head" ,"HTMLHead",array(
			"title" => "SOY CMS Password Remind "
		));

		$this->addImage("biglogo", array(
			"src" => SOY2PageController::createRelativeLink("css/img/logo_big.gif"),
		));

		DisplayPlugin::toggle("sendmail", !$isSended);
		DisplayPlugin::toggle("sended", $isSended);

		$this->addForm("remind_form");

		DisplayPlugin::toggle("is_message", !$isSended && strlen($errorMessage));
		$this->addLabel("message", array(
			"text" => $errorMessage,
		));
	}
}
