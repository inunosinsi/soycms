<?php

class RedirectPage extends CMSHTMLPageBase{

	var $userId;

	function RedirectPage(){

		//ログインIDの指定がなければただちにログイン画面へ
		if(!isset($_GET["userId"]) || strlen($_GET["userId"])<1){
			SOY2PageController::redirect("");
		}


		WebPage::WebPage();

		$this->addLabel("user_id", array(
			"text" => $_GET["userId"],
		));

		$this->addLabel("redirect_link", array(
			"text" => SOY2PageController::createLink("")
		));

		$this->addImage("biglogo", array(
			"src"=>SOY2PageController::createRelativeLink("css/img/logo_big.gif")
		));

	}

	function setUserId($userId){
		error_log(__LINE__." user id $userId");
		$this->userId = $userId;
		echo $userId;
	}
}

