<?php
class ResetPasswordPage extends WebPage{

	function doPost(){
		if(soy2_check_token()){
			$flashSession = SOY2ActionSession::getFlashSession();
			$flashSession->clearAttributes();
			$flashSession->resetFlashCounter();
			
			$result = SOY2ActionFactory::createInstance("ResetPasswordAction")->run();
			if($result->success()){
				$flashSession->setAttribute("isCompleted", true);
				SOY2PageController::jump("ResetPassword");
			}else{
				$flashSession->setAttribute("errorMessage", $result->getErrorMessage("error"));
			}
		}
	}
	
	function ResetPasswordPage() {
		
		WebPage::WebPage();

		$flashSession = SOY2ActionSession::getFlashSession();
		$isCompleted = is_null($flashSession->getAttribute("isCompleted")) ? false : $flashSession->getAttribute("isCompleted");
		$errorMessage = $flashSession->getAttribute("errorMessage");
		
		// トークンないときはトップに飛ばす
		if(!$isCompleted && (!isset($_GET["t"]) || strlen($_GET["t"]) < 1)){
			SOY2PageController::jump("");
		}
		
		HTMLHead::addLink("style", array(
				"rel" => "stylesheet",
				"type" => "text/css",
				"href" => SOY2PageController::createRelativeLink("./css/login/style.css") . "?" . SOYCMS_BUILD_TIME
		));
		
		$this->createAdd("head", "HTMLHead", array(
			"title" => "SOY CMS Reset Password ",
		));

		$this->addImage("biglogo", array(
			"src" => SOY2PageController::createRelativeLink("css/img/logo_big.gif"),
		));

		$this->addInput("token", array(
			"name" => "token",
			"value" => isset($_GET["t"]) ? $_GET["t"] : "",
		));
		
		$this->addModel("password", array(
			"visible" => !$isCompleted,
		));

		$this->addModel("completed", array(
			"visible" => $isCompleted,
		));

		$this->addForm("reset_form");

		$this->addLabel("message", array(
			"visible" => !$isCompleted,
			"text" => $errorMessage,
		));
	}
}
?>