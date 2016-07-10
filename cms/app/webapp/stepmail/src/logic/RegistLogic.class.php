<?php

class RegistLogic extends SOY2LogicBase{
	
	private $sendDao;
	
	function RegistLogic(){
		$this->sendDao = SOY2DAOFactory::create("StepMail_NextSendDAO");
	}
	
	function register($userId, $mailId){
		$obj = new StepMail_NextSend();
		$obj->setUserId($userId);

		//メールアドレスから初回のステップメールを取得する
		$step = $this->getFirstStepMailByMailId($mailId);
		$obj->setMailId($mailId);
		$obj->setStepId($step->getId());
		
		//初回送信時刻を取得
		$d = (int)$step->getDaysAfter();
		$obj->setNextSendDate(time() + $d * 24 * 60 * 60);
		
		try{
			$this->sendDao->insert($obj);
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	/**
	 * メールIDから初回のステップメールのオブジェクトを取得
	 * @param integer mailId
	 * @return Object StepMail_Step
	 */
	function getFirstStepMailByMailId($mailId){				
		try{
			return SOY2DAOFactory::create("StepMail_StepDAO")->getFirstStepMailByMailId($mailId);
		}catch(Exception $e){
			return new StepMail_Step();
		}
	}
	
	/**
	 * 半角英数字のmail_idからStepMail_MailオブジェクトのIDを取得する
	 */
	function getStepMailIdByMailId($stepmailId){
		try{
			return SOY2DAOFactory::create("StepMail_MailDAO")->getByMailId($stepmailId)->getId();
		}catch(Exception $e){
			return null;
		}
	}
}
?>