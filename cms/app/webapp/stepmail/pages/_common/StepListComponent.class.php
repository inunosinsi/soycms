<?php

class StepListComponent extends HTMLList{
	
	private $mailId;
	
	protected function populateItem($entity, $key, $step){
		
		$this->addLabel("step", array(
			"text" => $step
		));
		
		$this->addLabel("sum", array(
			"text" => self::stepDao()->getSumSendDate($this->mailId, $entity->getId())
		));
		
		$this->addLabel("title", array(
			"text" => $entity->getTitle()
		));
		
		$this->addLabel("overview", array(
			"text" => $entity->getOverview()
		));
		
//		$this->addLabel("content", array(
//			"text" => stepmail_get_first_line($entity->getContent())
//		));
		
		$detailLink = CMSApplication::createLink("Mail.Step." . $entity->getId() . "?mail_id=" . $this->mailId);
		if($step === 1) $detailLink .= "&first";
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));
		
		$this->addActionLink("remove_link", array(
			"link" => CMSApplication::createLink("Mail.Step.Remove." . $entity->getId() . "?mail_id=" . $this->mailId),
			"onclick" => "confirm('削除しますか？');"
		));
	}
		
	function setMailId($mailId){
		$this->mailId = $mailId;
	}
	
	private function stepDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_StepDAO");
		return $dao;
	}
}
?>