<?php

class AspAppUserCompletePage extends WebPage {

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
	}

	function execute(){
		parent::__construct();

		$res = false;

		//登録する
		if(isset($_GET["token"])){
			$logic = SOY2Logic::createInstance("site_include.plugin.asp_app.logic.AspAppRegisterLogic");
			$admin = $logic->getAdminByToken($_GET["token"]);

			$res = $logic->register($_GET["token"]);

			$mode = AspAppUtil::getSession("hidden_mode");	//モードがある場合はメールを送信しない
			if(isset($mode) && strlen($mode)){
				AspAppUtil::clearSession("hidden_mode");
				AspAppUtil::setSession("admin_id", $logic->getAdminId());
				header("location:" . AspAppUtil::getPageUri(AspAppUtil::MODE_DIRECT_REGISTRATION));
				exit;
			}

			//メールを送信
			if($res){
				$mail = AspAppUtil::getMailConfig(AspAppUtil::MAIL_REGISTER);
				$title = $mail["title"];
				$body = $mail["content"];
				$body = str_replace("##LOGIN_URL##", AspAppUtil::getLoginFormUrl(), $body);
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
