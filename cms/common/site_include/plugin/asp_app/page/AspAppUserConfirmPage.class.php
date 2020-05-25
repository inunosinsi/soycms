<?php

class AspAppUserConfirmPage extends WebPage {

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
		SOY2::import("site_include.plugin.asp_app.domain.AspAppPreRegisterDAO");
	}

	function doPost(){
		if(soy2_check_token()){

			//戻る
			if(isset($_POST["back"])){
				self::back();
			}

			//登録
			if(isset($_POST["register"])){
				$dao = SOY2DAOFactory::create("AspAppPreRegisterDAO");
				$obj = new AspAppPreRegister();
				$obj->setDataArray(AspAppUtil::get(true));
				$obj->setToken(AspAppUtil::generateToken($obj));

				try{
					$dao->insert($obj);
				}catch(Exception $e){
					try{
						$dao->update($obj);
					}catch(Exception $e){
						var_dump($e);
					}
				}


				$registerUrl = AspAppUtil::getPageUri(AspAppUtil::MODE_COMPLETE, true) . "?token=" . $obj->getToken();

				//いきなり本登録
				$mode = AspAppUtil::getSession("hidden_mode");
				if(isset($mode) && strlen($mode)){
					header("location:" . $registerUrl);
					exit;
				}

				//仮登録モード
				$mail = AspAppUtil::getMailConfig(AspAppUtil::MAIL_PRE);
				$title = $mail["title"];
				$body = $mail["content"];
				$body = str_replace("##REGISTER_URL##", $registerUrl, $body);
				$sendToName = "ASP版登録";	//@ToDo設定画面
				SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail(AspAppUtil::get()->getEmail(), $title, $body, $sendToName);

				header("location:" . AspAppUtil::getPageUri(AspAppUtil::MODE_PRE_REGISTRATION));
				exit;
			}
		}
	}

	private function back(){
		header("location:" . AspAppUtil::getPageUri());
		exit;
	}

	function execute(){
		parent::__construct();

		$mode = AspAppUtil::getSession("hidden_mode");

		$admin = AspAppUtil::get();
		if(isset($mode) && strlen($mode)){	//メールアドレス不要モード
			if(!strlen($admin->getUserId())) self::back();	//ログインIDがない場合は確認画面を表示させない
		}else{								//通常モード
			if(!strlen($admin->getEmail())) self::back();	//メールアドレスがない場合は確認画面を表示させない
		}

		$this->addForm("form");

		$this->addLabel("user_name", array(
			"text" => $admin->getName()
		));

		$this->addLabel("user_id", array(
			"text" => $admin->getUserId()
		));

		$this->addLabel("mail_address", array(
			"text" => $admin->getEmail()
		));

		$this->addLabel("password", array(
			"text" => AspAppUtil::buildPasswordString($admin->getUserPassword())
		));
	}

	/** @ToDo テンプレート編集モード **/
}
