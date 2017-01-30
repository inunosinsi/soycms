<?php

class UserListComponent extends HTMLList{

	private $appLimit;

	protected function populateItem($bean){

		$this->addInput("user_check", array(
			"name" => "users[]",
			"value" => $bean->getId(),
			"onchange" => '$(\'#users_operation\').show();',
			"visible" => $this->appLimit
		));
		
		$this->addLabel("id", array(
			"text" => $bean->getId()
		));
		
		$userName = $bean->getName();
		if($bean->getUserType() != SOYShop_User::USERTYPE_REGISTER){
			$userName .= "(仮登録)";
		}

		$this->addLabel("name", array(
			"text" => $userName
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
	
	function setAppLimit($appLimit){
		$this->appLimit = $appLimit;
	}
}
?>