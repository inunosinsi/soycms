<?php

class CompletePage extends CMSWebPageBase{

	function __construct(){

		if(soy2_check_token()){
			SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
				"target" => "admin"
			))->update();

			/**
			 * @データベースの変更後に何らかの操作が必要な場合
			 */
		}else{
			SOY2PageController::redirect("");
		}

		parent::__construct();

		$messages = CMSMessageManager::getMessages();
		$messages = array_merge($messages, array("データベースのバージョンアップが完了しました。"));
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
