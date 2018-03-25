<?php
SOY2::import("domain.admin.Administrator");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator.Detail");
		}

		parent::__construct();

		$this->outputMessage();

		$entities = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->getLimitedAdministratorList();

		//管理者がいないときはリストを隠して、メッセージを表示
		$this->addModel("main_table", array(
			"visible"=>(count($entities) > 0)
		));
		$this->addLabel("table_title", array(
			"text"=>CMSMessageManager::get("ADMIN_ADMIN_ID"),
			"visible"=>(count($entities) > 0)
		));
		$this->createAdd("list", "_common.Administrator.AdministratorListComponent", array(
			"list"	=> $entities,
			"sites"   => SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getSiteList(),
			"visible" => (count($entities) > 0)
		));
		$this->addLabel("no_administrator", array(
			"text"=>CMSMessageManager::get("ADMIN_MESSAGE_NO_USER"),
			"visible" => (count($entities) == 0)
		));

		$this->addLink("addAdministrator", array(
			"link"=>SOY2PageController::createLink("Administrator.Create"),
			"visible"=>UserInfoUtil::isDefaultUser()
		));

		//自分のパスワード変更
		$this->addLink("changepassword", array(
			"link" => SOY2PageController::createLink("Administrator.ChangePassword")
		));

		$this->addLink("reminderconfig", array(
			"link" => SOY2PageController::createLink("Administrator.Mail"),
			"visible" => UserInfoUtil::isDefaultUser(),
		));
	}

	/**
	 * メッセージ出力
	 */
	function outputMessage(){
		$messages = CMSMessageManager::getMessages();
		$this->addLabel("message", array(
			"text" => implode("\n",$messages),
			"visible" => !empty($messages)
		));
		$this->addModel("has_message", array(
				"visible" => count($messages),
		));
	}
}
