<?php

class UserListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLabel("id", array(
			"text" => $bean->getId()
		));

		$this->addLabel("name", array(
			"text" => $bean->getName()
		));

		$this->addLabel("mailaddress", array(
			"text" => $bean->getMailAddress(),
			"title" => $bean->getMailAddress()
		));

		$this->addLabel("attribute1", array(
			"text" => $bean->getAttribute1()
		));

		$this->addLabel("attribute2", array(
			"text" => $bean->getAttribute2()
		));

		$this->addLabel("attribute3", array(
			"text" => $bean->getAttribute3()
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("User.Detail") . "/" . $bean->getId()
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("User.Remove") . "/" . $bean->getId(),
			"onclick" => "return confirm('削除してよろしいですか？')"
		));
	}
}
?>