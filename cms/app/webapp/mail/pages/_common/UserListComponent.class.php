<?php

class UserListComponent extends HTMLList{

	private $connect;	//SOY Shop連携

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

		$this->addLabel("not_send", array(
			"text" => (is_numeric($bean->getNotSend()) && $bean->getNotSend() == 0) ? "許可" : "拒否"
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
			"link" => SOY2PageController::createLink("mail.User.Detail") . "/" . $bean->getId()
		));

		$this->addModel("no_connect", array(
			"visible" => !$this->connect
		));
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("mail.User.Remove") . "/" . $bean->getId(),
			"onclick" => "return confirm('削除してよろしいですか？')"
		));
	}

	function setConnect($connect){
		$this->connect = $connect;
	}
}
