<?php

class PasswordPage extends CMSUpdatePageBase{

	private $failed = false;
	private $admin;

	function doPost(){

		if(soy2_check_token() && $this->updatePassword()){
			CMSMessageManager::addMessage(CMSMessageManager::get("CHANGE_PASSWORD_SUCCESS")." - ".( $this->admin->getName() ? $this->admin->getName() . " (".$this->admin->getUserId().")" : $this->admin->getUserId()));
			$this->jump("Administrator");
		}else{
			$this->failed = true;
		}
	}

	function __construct($arg) {
		$adminId = (isset($arg[0])) ? $arg[0] : null;

		if(strlen($adminId) < 1){
			$this->jump("Administrator");
		}

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator");
		}

		//自身のパスワード変更は不可（現在のパスワードを知らなくても変更できてしまう）
		if($adminId == UserInfoUtil::getUserId()){
			$this->jump("Administrator.ChangePassword");
		}

		//管理者情報
		$this->admin = $this->getAdministratorById($adminId);
		if(!$this->admin){
			$this->jump("Administrator");
		}

		parent::__construct();

		$this->addForm("change_password_form");

		$this->addModel("error", array(
			"visible" => $this->failed
		));

		$this->addInput("administratorId",array(
			"name" => "administratorId",
			"value" => $adminId,
		));
		$this->addLabel("userId",array(
			"text" => $this->admin->getUserId(),
		));
		$this->addLabel("userName",array(
			"text" => $this->admin->getName(),
		));

	}

	/**
	 * 指定した管理者のパスワードを変更します
	 * Administrator
	 * @return boolean
	 */
	function updatePassword(){
		$action = SOY2ActionFactory::createInstance("Administrator.UpdatePasswordAction");
		$result = $action->run();

		return $result->success();
	}

	/**
	 * 管理者情報取得
	 * @param int Administrator.id
	 * @return Administrator | boolean false
	 */
	private function getAdministratorById($id){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			return $dao->getById($id);
		}catch(Exception $e){
			return false;
		}
	}

}
