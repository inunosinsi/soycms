<?php

class AspUserCompletePage extends WebPage {

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
	}

	function execute(){
		parent::__construct();

		$res = false;

		//登録する
		if(isset($_GET["token"])){
			$logic = SOY2Logic::createInstance("site_include.plugin.asp.logic.AspRegisterLogic");
			$admin = $logic->getAdminByToken($_GET["token"]);

			$res = $logic->register($_GET["token"]);

			//メールを送信
			if($res){
				$mail = AspUtil::getMailConfig(AspUtil::MAIL_REGISTER);
				$title = $mail["title"];
				$body = $mail["content"];
				$body = str_replace("##LOGIN_URL##", AspUtil::getLoginFormUrl(), $body);
				$body = str_replace("##ACCOUNT##", $admin->getEmail(), $body);
				$sendToName = "ASP版登録";	//@ToDo設定画面
				SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail($admin->getEmail(), $title, $body, $sendToName);
			}
		}

		DisplayPlugin::toggle("successed", $res);
		DisplayPlugin::toggle("failed", !$res);
	}

	/** @ToDo テンプレート編集モード **/
}
