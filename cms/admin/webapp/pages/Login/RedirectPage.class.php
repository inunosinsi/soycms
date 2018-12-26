<?php

class RedirectPage extends CMSHTMLPageBase{

	var $userId;

	function __construct(){

		//ログインIDの指定がなければただちにログイン画面へ
		if(!isset($_GET["userId"]) || strlen($_GET["userId"])<1){
			SOY2PageController::redirect("");
		}

		define("HEAD_TITLE", CMSUtil::getCMSName() . " Redirect ");
		parent::__construct();

		$this->addLabel("user_id", array(
			"text" => $_GET["userId"],
		));

		$this->addLabel("redirect_link", array(
			"text" => SOY2PageController::createLink("")
		));

		$this->addImage("biglogo", array(
			"src" => CMSUtil::getLogoFile()
		));
	}

	function setUserId($userId){
		error_log(__LINE__." user id $userId");
		$this->userId = $userId;
		echo $userId;
	}
}
