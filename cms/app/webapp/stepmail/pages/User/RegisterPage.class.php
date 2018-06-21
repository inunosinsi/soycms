<?php

class RegisterPage extends WebPage{

	private $id;
	private $registLogic;
	private $userLogic;

	private $detail;	//$_POST["Detail"]の値を入れておく

	function doPost(){

		if(soy2_check_token() && isset($_POST["Detail"])){
			$this->detail = $_POST["Detail"];

			$userId = $this->userLogic->getUserIdByMailAddress($this->detail["mailAddress"]);

			//顧客名簿からユーザを取得できなかった場合は登録する
			if(is_null($userId)) $userId = $this->userLogic->register($this->detail["mailAddress"]);

			//登録する
			if(isset($this->detail["mailId"]) && $this->registLogic->register($userId, $this->detail["mailId"])){
				CMSApplication::jump("User?successed");
			}
		}
	}

	function __construct(){
		$this->userLogic = SOY2Logic::createInstance("logic.UserLogic");
		$this->registLogic = SOY2Logic::createInstance("logic.RegistLogic");

		parent::__construct();

		DisplayPlugin::toggle("failed", (is_array($this->detail) && count($this->detail)));

		$this->addForm("form");

		$this->addInput("mail_address", array(
			"name" => "Detail[mailAddress]",
			"value" => (isset($this->detail["mailAddress"])) ? $this->detail["mailAddress"] : null
		));

		$this->addSelect("stepmail_type", array(
			"name" => "Detail[mailId]",
			"options" => self::getStepMailList(),
			"selected" => (isset($this->detail["mailId"])) ? $this->detail["mailId"] : false
		));

		$this->addModel("submit_button", array(
			"type" => "submit",
			"attr:value" => (isset($this->id)) ? "更新" : "登録"
		));

		$this->post = null;
	}

	private function getStepMailList(){
		try{
			$array = SOY2DAOFactory::create("StepMail_MailDAO")->get();
		}catch(Exception $e){
			return array();
		}

		if(count($array) === 0) return array();

		$dao = SOY2DAOFactory::create("StepMail_StepDAO");

		$list = array();
		foreach($array as $obj){
			try{
				$array = $dao->getByMailId($obj->getId());
			}catch(Exception $e){
				continue;
			}
			if(count($array)) $list[$obj->getId()] = $obj->getTitle();
		}

		return $list;
	}
}
