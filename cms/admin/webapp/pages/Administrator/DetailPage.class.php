<?php

class DetailPage extends CMSUpdatePageBase{

	private $adminId;
	private $failed = false;

	function doPost(){
		if(UserInfoUtil::getUserId() != $this->adminId && !UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator");
			exit;
		}

		if(soy2_check_token()){
			if(isset($_POST["tokenLogin"])){
				SOY2::import("util.TokenLoginUtil");
				if(isset($_POST["tokenLogin"]["create"])){
					$res = TokenLoginUtil::saveTokenLoginByUserId($this->adminId);
				}else if(isset($_POST["tokenLogin"]["remove"])){
					$res = TokenLoginUtil::removeTokenLoginByUserId($this->adminId);
				}else if(isset($_POST["tokenLogin"]["update"])){
					$res = TokenLoginUtil::updateTokenLoginByUserId($this->adminId);
				}
			}else{
				$res = self::updateAdministrator();
			}

			if($res) {
				$this->addMessage("UPDATE_SUCCESS");
				$this->jump("Administrator.Detail." . $this->adminId);
				exit;
			}
		}

		$this->failed = true;
	}

	function __construct($arg) {
		$adminID = (isset($arg[0])) ? $arg[0] : null;
		if(!UserInfoUtil::isDefaultUser() || strlen($adminID) < 1) $adminID = UserInfoUtil::getUserId();
		if(is_null($adminID)){
			//データベースから直接取得する 初期管理者は必ず1
			try{
				$adminID = SOY2DAOFactory::create("admin.AdministratorDAO")->getById(1)->getId();
			}catch(Exception $e){
				$adminID = null;
			}
		}

		$result = $this->run("Administrator.DetailAction", array("adminId" => $adminID));
		$admin = $result->getAttribute("admin");
		$this->adminId = $adminID;

		parent::__construct();

		$showInputForm = UserInfoUtil::isDefaultUser() || $this->adminId == UserInfoUtil::getUserId();

		$this->addInput("userId", array(
			"name" => "userId",
			"value"=>$admin->getUserId(),
			"visible"=> $showInputForm
		));
		$this->addInput("name", array(
			"name" => "name",
			"value"=>$admin->getName(),
			"visible"=> $showInputForm
		));
		$this->addInput("email", array(
			"name" => "email",
			"value"=>$admin->getEmail(),
			"visible"=> $showInputForm
		));

		//カスタムフィールド
		$this->addLabel("customfield", array(
			"html" => self::buildCustomField($admin->getId())
		));

		$this->addLabel("userId_text", array(
			"text"=>(strlen($admin->getUserId()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getUserId(),
			"visible"=> !$showInputForm
		));
		$this->addLabel("name_text", array(
			"text"=>(strlen($admin->getName()) == 0 ) ? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getName(),
			"visible"=> !$showInputForm
		));
		$this->addLabel("email_text", array(
			"text"=>(strlen($admin->getEmail()) == 0 )? CMSMessageManager::get("ADMIN_NO_SETTING") : $admin->getEmail(),
			"visible"=> !$showInputForm
		));

		// token login
		SOY2::import("util.TokenLoginUtil");
		DisplayPlugin::toggle("token_login", TokenLoginUtil::isTokenLoginMode());
		$isTokenLoginUser = TokenLoginUtil::isTokenLoginUser($admin->getId());
		DisplayPlugin::toggle("no_token_login_user", !$isTokenLoginUser);
		DisplayPlugin::toggle("token_login_user", $isTokenLoginUser);

		$tokenLoginUrl = TokenLoginUtil::buildEntpointUrlByUserId($admin->getId());
		$this->addLink("token_login_url", array(
			"link" => $tokenLoginUrl,
			"text" => $tokenLoginUrl
		));

		$this->addLabel("token_login_time_limit", array(
			"text" => date("Y-m-d H:i:s", TokenLoginUtil::getLoginUrlExpiryByUserId($admin->getId()))
		));

		$this->addModel("show_userid_input", array(
			"attr:class" => $showInputForm ? "" : "no_example"
		));
		$this->addModel("show_userid_input_example", array(
			"visible" => $showInputForm
		));

		$this->addModel("button_toggle", array(
			"visible"=> $showInputForm
		));

		//このユーザでログインボタン
		DisplayPlugin::toggle("instead_login", (UserInfoUtil::isDefaultUser() && UserInfoUtil::getUserId() != $adminID));
		$this->addLink("instead_login_link", array(
			"link" => SOY2PageController::createLink("Administrator.InsteadLogin.".$adminID),
			"onclick" => "return confirm('" . UserInfoUtil::getLoginId() . "をログアウトしてから" . $admin->getUserId() ."でログインしますがよろしいですか？');"
		));

		$this->addForm("detailForm");

		$this->addModel("error", array(
			"visible" => $this->failed
		));

		$messages = CMSMessageManager::getMessages();
		$this->addLabel("message", array(
			"text" => implode("\n", $messages),
			"visible" => !empty($messages)
		));

		$this->addModel("has_message_or_error",array(
			"visible" => $this->failed || !empty($messages),
		));

		//ページの末尾にカスタムHTMLを追加
		if(file_exists(SOY2::RootDir() . "config/administrator.detail.custom.php")) include_once(SOY2::RootDir() . "config/administrator.detail.custom.php");
		$this->addLabel("custom_html", array(
			"html" => (isset($customHtml) && strlen($customHtml)) ? $customHtml : ""
		));
	}

	function setAdminId($adminId) {
		$this->adminId = $adminId;
	}

	private function updateAdministrator(){
		$result = $this->run("Administrator.UpdateAction", array("adminId" => $this->adminId));
		return $result->success();
	}

	private function buildCustomField($adminId){
		$attrDao = SOY2DAOFactory::create("admin.AdministratorAttributeDAO");
		$configs = AdministratorAttributeConfig::load();
		if(!count($configs)) return array();

		try{
			$attrs = $attrDao->getByAdminId($adminId);
		}catch(Exception $e){
			$attrs = array();
		}

		$html = array();
		foreach($configs as $config){
			$value = (isset($attrs[$config->getFieldId()])) ? $attrs[$config->getFieldId()]->getValue() : "";
			$html[] = $config->getForm($value);
		}

		return implode("\n", $html);
	}
}
