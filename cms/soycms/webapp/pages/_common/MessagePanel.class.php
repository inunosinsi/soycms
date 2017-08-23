<?php

class MessagePanel extends CMSWebPageBase{

	function execute(){

		/**
		 * メッセージ表示
		 */

		$messages = CMSMessageManager::getMessages();
		$error  = CMSMessageManager::getErrorMessages();

		$this->createAdd("success_message_list","MessageListInMessagePanel",array(
				"list" => $messages,
		));
		$this->createAdd("error_message_list","MessageListInMessagePanel",array(
				"list" => $error,
		));

		$this->addModel("hasMessage", array(
				"visible" => (count($error) > 0 || count($messages) > 0),
		));
	}
}

class MessageListInMessagePanel extends HTMLList{

	protected function populateItem($entity, $key){
		$this->addLabel("message", array(
				"html" => $entity,
		));

		return ( strlen($entity) > 0 );
	}
}
