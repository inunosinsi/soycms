<?php

class MailTypeListComponent extends HTMLList{
	
	function populateItem($entity){
		
		$fieldId = (isset($entity["id"])) ? $entity["id"] : "";
		$this->addLabel("mail_id", array(
			"text" => $fieldId
		));
		
		$this->addLabel("mail_title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));
		
		/* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $fieldId
		));
		
		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("Config.Mail.User?type=" . $fieldId . "&plugin"),
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=common_add_mail_type&remove&field_id=" . $fieldId),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
?>