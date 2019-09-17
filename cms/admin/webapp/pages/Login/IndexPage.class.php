<?php

class IndexPage extends CMSHTMLPageBase{
	var $message = "";
	var $username;

	function doPost(){
		$action = SOY2ActionFactory::createInstance('LoginAction');
		$result = $action->run();

		//ログイン
		if($result->success()){
			SOY2PageController::redirect("");
		}else{
			//失敗したときは2秒待たせる
			sleep(2);
		}
		$this->message = CMSMessageManager::get("ADMIN_FAILURE_TO_LOGIN");
		$this->username = $result->getAttribute('username');

	}

	function __construct(){
		//ログインしていたらルートに飛ばす
		if(UserInfoUtil::isLoggined()){
			SOY2PageController::jump("");
		}


		//自動ログイン
		if(SOY2ActionFactory::createInstance('AutoLoginAction')->run()->success()){
			SOY2PageController::redirect("");
		}

		define("HEAD_TITLE", CMSUtil::getCMSName() . " Login");
		parent::__construct();


		//CSS読み込み
		// HTMLHead::addLink("login_style",array(
		// 		"rel" => "stylesheet",
		// 		"type" => "text/css",
		// 		"href" => SOY2PageController::createRelativeLink("./css/login/style.css")."?".SOYCMS_BUILD_TIME
		// ));
		// $this->createAdd("head" ,"HTMLHead",array(
		// 	"title" => "SOY CMS Login"
		// ));


		//フォームの作成
		$this->addForm("AuthForm");

		$this->addInput("username", array(
			"name" => "Auth[name]",
			"value" => $this->username
		));
		$this->addInput("password", array(
			"name" => "Auth[password]",
			"value" => ""
		));

		$this->addLabel("message", array(
			"html" => $this->message,
			"visible" => strlen($this->message)
		));

		$this->addLabel("reminder", array(
			"html" => "<a href='".SOY2PageController::createLink("PasswordRemind")."'>パスワードを忘れた場合</a>",
			"visible" => SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->hasMailAddress() &&
			!is_null(SOY2Logic::createInstance("logic.mail.MailConfigLogic")->get()),
		));

		$this->addModel("biglogo", array(
    		"src" => CMSUtil::getLogoFile()
    	));

		//auto_loginのチェック
		DisplayPlugin::toggle("auto_login", self::_isAutoLogin());
		$this->addCheckBox("auto_login", array(
			"name" => "auto_login",
			"value" => 1,
			"label" => "次回から自動的にログインする"
		));
	}

    /**
     * Overwrite CMSHTMLPageBase::getTemplateFilePath
     */
    function getTemplateFilePath(){

		if(defined("SOYCMS_LANGUAGE_DIR")){
			$dir = dirname($this->getClassPath());
			if(strlen($dir) > 0) $dir .= '/';

			$soy2html_root = SOY2HTMLConfig::PageDir();
			$language_root = SOYCMS_LANGUAGE_DIR.SOY2HTMLConfig::Language() . "/Login/";
			$custom_lang_html = str_replace($soy2html_root, $language_root, $dir) . get_class($this) . ".html";
			if(file_exists($custom_lang_html)){
				return $custom_lang_html;
			}
		}

		return 	parent::getTemplateFilePath();
    }

	private function _isAutoLogin(){
		if(defined("SOYCMS_ASP_MODE")) return false;	//ASPモードの場合はAutoLoginは利用できない

		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("SELECT * FROM AutoLogin LIMIT 1;");
			return true;
		}catch(Exception $e){
			return false;
		}
	}
}
