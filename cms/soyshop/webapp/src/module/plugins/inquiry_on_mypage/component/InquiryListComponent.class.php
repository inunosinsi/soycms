<?php

class InquiryListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addLabel("tracking_number", array(
			"text" => $entity->getTrackingNumber()
		));

		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getCreateDate())
		));

		$this->addLink("user_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $entity->getUserId())
		));

		$this->addLabel("user_name", array(
			"text" => soyshop_get_user_object($entity->getUserId())->getName()
		));

		$this->addLink("mail_detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.inquiry_on_mypage." . $entity->getMailLogId())
		));

		if($entity->getMailLogId() == 0) return false;
	}
}
