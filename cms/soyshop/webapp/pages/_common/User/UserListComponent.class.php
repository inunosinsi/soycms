<?php

class UserListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addInput("user_check", array(
			"name" => "users[]",
			"value" => $bean->getId(),
			"onchange" => '$(\'#users_operation\').show();',
			"visible" => AUTH_OPERATE
		));

		$this->addLabel("id", array(
			"text" => $bean->getId()
		));

		$userName = $bean->getName();
		if($bean->getUserType() != SOYShop_User::USERTYPE_REGISTER) $userName .= "(仮登録)";
		if($bean->getIsPublish() != SOYShop_User::USER_IS_PUBLISH) $userName .= "(非公開)";

		$this->addLabel("name", array(
			"text" => $userName
		));

		$this->addLabel("mailaddress", array(
			"text" => $bean->getMailAddress(),
			"title" => $bean->getMailAddress(),
			"style" => (strpos($bean->getMailAddress(), DUMMY_MAIL_ADDRESS_DOMAIN) !== false) ? "color:#ABABAB !important" : null
		));

		$this->addLabel("account_id", array(
			"text" => $bean->getAccountId()
		));

		$this->addModel("show_account_id_item", array(
			"visible" => (defined("SHOW_ACCOUNT_ID_ITEM") && SHOW_ACCOUNT_ID_ITEM)
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
