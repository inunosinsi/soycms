<?php

class CreatePage extends CMSUpdatePageBase{

	var $failed = false;

	function doPost(){

		if(soy2_check_token()){
			$res = self::createAdministrator();

			if($res !== false){
				$this->addMessage("CREATE_SUCCESS");
				SOY2PageController::jump("Administrator.SiteRole." . $res);
			}
		}

		$this->failed = true;
	}

	function __construct(){
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator");
		}
		parent::__construct();
		$this->addForm("change_password_form");

		$this->addModel("error", array(
			"visible" => $this->failed
		));

		//カスタムフィールド
		$this->addLabel("customfield", array(
			"html" => self::buildCustomField()
		));

	}

	/**
	 * 管理者を追加する。
	 * Administrator.CreateActionを呼び出す
	 */
	private function createAdministrator(){
		$action = SOY2ActionFactory::createInstance("Administrator.CreateAction");
		$result = $action->run();

		if($result->success()){
			return $result->getAttribute("id");
		}else{
			return false;
		}
	}

	private function buildCustomField(){
		SOY2::import("domain.admin.AdministratorAttribute");
		$configs = AdministratorAttributeConfig::load();
		if(!count($configs)) return array();

		$html = array();
		foreach($configs as $config){
			$html[] = $config->getForm("");
		}

		return implode("\n", $html);
	}
}
