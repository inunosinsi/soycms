<?php

class AspUserConfirmPage extends WebPage {

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
		SOY2::import("site_include.plugin.asp.domain.AspPreRegisterDAO");
	}

	function doPost(){
		if(soy2_check_token()){

			//戻る
			if(isset($_POST["back"])){
				self::back();
			}

			//登録
			if(isset($_POST["register"])){
				$dao = SOY2DAOFactory::create("AspPreRegisterDAO");
				$obj = new AspPreRegister();
				$obj->setSiteId(AspUtil::getSiteId());
				$obj->setDataArray(AspUtil::get(true));
				$obj->setToken(AspUtil::generateToken($obj));

				try{
					$dao->insert($obj);
				}catch(Exception $e){
					try{
						$dao->update($obj);
					}catch(Exception $e){
						var_dump($e);
					}
				}


				$mail = AspUtil::getMailConfig(AspUtil::MAIL_PRE);
				$title = $mail["title"];
				$body = $mail["content"];
				$body = str_replace("##REGISTER_URL##", AspUtil::getPageUri(AspUtil::MODE_COMPLETE, true) . "?token=" . $obj->getToken(), $body);
				$sendToName = "ASP版登録";	//@ToDo設定画面
				SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail(AspUtil::get()->getEmail(), $title, $body, $sendToName);

				header("location:" . AspUtil::getPageUri(AspUtil::MODE_PRE_REGISTRATION));
				exit;
			}
		}
	}

	private function back(){
		header("location:" . AspUtil::getPageUri());
		exit;
	}

	function execute(){
		parent::__construct();

		$admin = AspUtil::get();
		if(!strlen($admin->getEmail())) self::back();	//メールアドレスがない場合は確認画面を表示させない

		$this->addForm("form");

		$this->addLabel("user_name", array(
			"text" => $admin->getName()
		));

		$this->addLabel("mail_address", array(
			"text" => $admin->getEmail()
		));

		$this->addLabel("password", array(
			"text" => AspUtil::buildPasswordString($admin->getUserPassword())
		));

		$this->addLabel("site_url", array(
			"text" => AspUtil::getSiteUrl()
		));

		$this->addLabel("site_id", array(
			"text" => AspUtil::getSiteId()
		));
	}

	/** @ToDo テンプレート編集モード **/
}
