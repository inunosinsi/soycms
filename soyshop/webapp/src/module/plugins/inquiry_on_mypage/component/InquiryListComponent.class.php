<?php

class InquiryListComponent extends HTMLList {

	private $userNameList = array();

	protected function populateItem($entity) {

		$this->addLabel("tracking_number", array(
			"text" => $entity->getTrackingNumber()
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s", $entity->getCreateDate()) : ""
		));

		$userId = (is_numeric($entity->getUserId())) ? (int)$entity->getUserId() : 0;
		$this->addLink("user_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $userId)
		));

		$this->addLabel("user_name", array(
			"text" => (isset($this->userNameList[$userId])) ? $this->userNameList[$userId] : ""
		));

		$this->addLabel("is_confirm", array(
			"text" => ($entity->getIsConfirm()) ? "確認済み" : "未確認"
		));

		$this->addLink("mail_detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.inquiry_on_mypage." . $entity->getMailLogId())
		));

		if($entity->getMailLogId() == 0) return false;
	}

	function setUserNameList($userNameList){
		$this->userNameList = $userNameList;
	}
}
