<?php

class HistoryBoxMailListComponent extends HTMLList{

	var $errorMailDAO;

	function getErrorMail($id){
		if(!$this->errorMailDAO)$this->errorMailDAO = SOY2DAOFactory::create("ErrorMailDAO");
		try{
			return $this->errorMailDAO->getErrorMailCountByMailId((int)$id);
		}catch(Exception $e){
			return "-";
		}
	}

	protected function populateItem($bean){

		$this->createAdd("title","HTMLLink",array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail.MailDetail") . "/" . $bean->getId()
		));

		$this->createAdd("content","HTMLLabel",array(
			"text" => $bean->getMailContent()
		));

		$this->createAdd("update_date","HTMLLabel",array(
			"text" => date("Y-m-d", $bean->getUpdateDate())
		));

		$this->createAdd("send_start_date","HTMLLabel",array(
			"text" => date("Y-m-d H:i:s",$bean->getSendDate())
		));

		$this->createAdd("send_end_date","HTMLLabel",array(
			"text" => date("Y-m-d H:i:s",$bean->getSendedDate())
		));

		$this->createAdd("mail_count","HTMLLabel",array(
			"text" => $bean->getMailCount()
		));

		$this->createAdd("error_count","HTMLLabel",array(
			"text" => $this->getErrorMail($bean->getId())
		));

		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("mail.Mail") . "/" . $bean->getId()
		));

		$this->createAdd("remove_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("mail.Mail.Remove") . "/" . $bean->getId(),
			"onclick" => "削除してもよろしいですか？",
		));
	}
}

?>