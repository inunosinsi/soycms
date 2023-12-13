<?php

class IndexPage extends CMSWebPageBase{

	function __construct(){

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		//更新が不要なら戻る
		if(!SOY2LogicContainer::get("logic.db.UpdateDBLogic", array("target" => "admin"))->hasUpdate()) SOY2PageController::jump("");

		parent::__construct();

		$this->addActionLink("update_link", array(
			"link" => SOY2PageController::createLink("Upgrade.Admin.Complete")
		));

		$messages = CMSMessageManager::getMessages();
		$messages = array_merge($messages, array("管理側のデータベースのバージョンアップが必要です。"));
		$errors = CMSMessageManager::getErrorMessages();
		$this->addLabel("message", array(
				"text" => implode($messages),
				"visible" => (count($messages) > 0)
		));
		$this->addLabel("error", array(
				"text" => implode($errors),
				"visible" => (count($errors) > 0)
		));

		$this->addModel("has_message_or_error", array(
				"visible" => count($messages) || count($errors),
		));
	}
}
