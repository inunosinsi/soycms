<?php

class UserListComponent extends HTMLList{
	
	protected function populateItem($entity, $key, $step){
		
		$user = self::getUser($entity->getUserId());
		
		$this->addLabel("name", array(
			"text" => (strlen($user->getName())) ? $user->getName() : "名無し"
		));
		
		$this->addLink("mail_address", array(
			"link" => "mailto:" . $user->getMailAddress(),
			"text" => $user->getMailAddress()
		));
		
		$this->addLink("step_mail_title", array(
			"link" => CMSApplication::createLink("Mail.Detail." . $entity->getMailId()),
			"text" => self::getStepMailTitle($entity->getMailId())
		));
		
		$this->addLabel("next_send_date", array(
			"text" => date("Y-m-d", $entity->getNextSendDate())
		));
		
		$this->addLabel("next", array(
			"text" => self::getNextTh($entity->getMailId(), $entity->getStepId())
		));
		
		$this->addLabel("total", array(
			"text" => self::getTotal($entity->getMailId())
		));
		
		$this->addActionLink("cancel_link", array(
			"link" => CMSApplication::createLink("User.Cancel." . $entity->getId()),
			"onclick" => "return confirm('メールの配信を停止しますか？');"
		));
	}
	
	private function getUser($userId){
		try{
			return self::userDao()->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	private function getStepMailTitle($mailId){
		try{
			return self::mailDao()->getById($mailId)->getTitle();
		}catch(Exception $e){
			return "";
		}
	}
	
	private function getNextTh($mailId, $stepId){
		return self::stepDao()->countNextTh($mailId, $stepId);
	}
	
	private function getTotal($mailId){
		try{
			return self::stepDao()->countStepByMailId($mailId);
		}catch(Exception $e){
			return 0;
		}
	}
	
	private function userDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_UserDAO");
		return $dao;
	}
	
	private function mailDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_MailDAO");
		return $dao;
	}
	
	private function stepDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_StepDAO");
		return $dao;
	}
}