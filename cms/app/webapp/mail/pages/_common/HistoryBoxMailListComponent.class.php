<?php

class HistoryBoxMailListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLink("title", array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail.MailDetail") . "/" . $bean->getId()
		));

		$this->addLabel("content", array(
			"text" => $bean->getMailContent()
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($bean->getUpdateDate())) ? date("Y-m-d", $bean->getUpdateDate()) : ""
		));

		$this->addLabel("send_start_date", array(
			"text" => (is_numeric($bean->getSendDate())) ? date("Y-m-d H:i:s", $bean->getSendDate()) : ""
		));

		$this->addLabel("send_end_date", array(
			"text" => (is_numeric($bean->getSendedDate())) ? date("Y-m-d H:i:s", $bean->getSendedDate()) : ""
		));

		$this->addLabel("mail_count", array(
			"text" => $bean->getMailCount()
		));

		$this->addLabel("error_count", array(
			"text" => self::_getErrorMail($bean->getId())
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("mail.Mail") . "/" . $bean->getId()
		));

		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("mail.Mail.Remove") . "/" . $bean->getId(),
			"onclick" => "削除してもよろしいですか？",
		));
	}

	private function _getErrorMail($id){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("ErrorMailDAO");
		if(!is_numeric($id)) return "-";
		try{
			return $dao->getErrorMailCountByMailId((int)$id);
		}catch(Exception $e){
			return "-";
		}
	}
}
