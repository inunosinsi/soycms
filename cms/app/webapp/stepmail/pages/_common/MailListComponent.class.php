<?php

class MailListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLabel("title", array(
			"text" => $entity->getTitle()
		));
		
		$this->addLabel("mail_id", array(
			"text" => $entity->getMailId()
		));
		
		$this->addLabel("overview", array(
			"text" => $entity->getOverview()
		));
		
		$id = $entity->getId();
		$this->addLabel("send_count", array(
			"text" => (isset($id) && is_numeric($id)) ? self::stepDao()->countStepByMailId($entity->getId()) : 0
		));
		
		$this->addLink("detail_link", array(
			"link" => CMSApplication::createLink("Mail.Detail." . $entity->getId())
		));
		
		$this->addActionLink("remove_link", array(
			"link" => CMSApplication::createLink("Mail.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
	
	private function stepDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_StepDAO");
		return $dao;
	}
}
?>