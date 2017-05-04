<?php

class SendMailLogic extends SOY2LogicBase{
	
	private $sendDao;
	private $stepDao;
	private $historyDao;
	private $mailLogic;
	
	private $mails = array();	/** データベースから取り出したステップメールを入れておく array($key => array("title", "content"))**/
	
	function __construct(){
		$this->sendDao = SOY2DAOFactory::create("StepMail_NextSendDAO");
		$this->stepDao = SOY2DAOFactory::create("StepMail_StepDAO");
		$this->historyDao = SOY2DAOFactory::create("StepMail_SendHistoryDAO");
	}
	
	function execute(){
		
		$nextSends = self::getStepMailOfSendSchedule();
		
		//送信予定のオブジェクトがない場合はfalseを返す
		if(!count($nextSends)) return false;
		
		//SOY ShopからMailLogicを呼び出す
		self::prepareMailConfig();
		
		//フッターの取得
		$footer = StepMail_DataSets::get("mail_footer", null);
		if(!isset($footer)) $footer = "\n" . $footer;
		
		//メールの文面の置換用
		$order = new SOYShop_Order();
		foreach($nextSends as $obj){
			self::setMailContent($obj->getStepId());
			$user = self::getUser($obj->getUserId());
			$content = $this->mails[$obj->getStepId()]["content"] . $footer;	//ここでメール文面にフッタを付与しておく
			$content = $this->mailLogic->convertMailContent($content, $user, $order);
			
			try{
				$this->mailLogic->sendMail($user->getMailAddress(), $this->mails[$obj->getStepId()]["title"], $content);
				$obj->setIsSended(StepMail_NextSend::IS_SENDED);
				$this->sendDao->update($obj);
			}catch(Exception $e){
				continue;
			}
						
			//次回送信日を登録する
			self::registerNextSend($obj->getMailId(), $obj->getStepId(), $obj->getUserId());
			
			//送信履歴に登録する
			self::logginSendMail($obj->getId());
		}
	}
	
	private function getStepMailOfSendSchedule(){
		try{
			return $this->sendDao->getStepMailOfSendSchedule(self::getTodayTimestamp("end"));
		}catch(Exception $e){
			return array();
		}
	}
	
	//本日のはじめと終わりのタイムスタンプを取得
	private function getTodayTimestamp($mode = "start"){
		$t = explode("-", date("Y-m-d", time()));
		if($mode == "start"){
			return mktime(0, 0, 0, $t[1], $t[2], $t[0]);
		}else{
			return mktime(23, 59, 59, $t[1], $t[2], $t[0]);
		}
	}
	
	private function setMailContent($stepId){
		if(!isset($this->mails[$stepId])){
			try{
				$stepObj = $this->stepDao->getById($stepId);
			}catch(Exception $e){
				return;
			}
			
			$this->mails[$stepObj->getId()] = array("title" => $stepObj->getTitle(), "content" => $stepObj->getContent());
		}
	}
	
	private function getUser($userId){
		if(!$this->userLogic) $this->userLogic = SOY2Logic::createInstance("logic.UserLogic");
		return $this->userLogic->getUser($userId);
	}
	
	private function registerNextSend($mailId, $stepId, $userId){
		$newStep = self::getNextStep($mailId, $stepId);
		if(is_null($newStep->getId())) return false; //先のステップがない場合はfalseを返す
		
		$new = new StepMail_NextSend();
		$new->setUserId($userId);
		$new->setMailId($mailId);
		$new->setStepId($newStep->getId());
		$new->setNextSendDate(time() + $newStep->getDaysAfter() * 24 * 60 * 60);
			
		try{
			$this->sendDao->insert($new);
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	private function getNextStep($mailId, $stepId){
		try{
			return $this->stepDao->getNextStep($mailId, $stepId);
		}catch(Exception $e){
			return new StepMail_Step();
		}
	}
	
	private function logginSendMail($sendId){
		$obj = new StepMail_SendHistory();
		$obj->setSendId($sendId);
		try{
			$this->historyDao->insert($obj);
		}catch(Exception $e){
			var_dump($e);
		}
	}
	
	private function prepareMailConfig(){
		if(!$this->mailLogic){
			SOY2::RootDir(SOYSHOP_WEBAPP . "src/");
			SOY2::imports("domain.logging.*");
			SOY2::imports("domain.config.*");
			SOY2::import("domain.order.SOYShop_Order");
			include_once(SOY2::RootDir() . "base/func/common.php");
			$this->mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
			
			//STEPMAILの方に戻す
			SOY2::RootDir(STEPMAIL_SRC);
			
			//フッターの情報を取得出来る様にしておく
			SOY2::import("domain.StepMail_DataSets");
		}
	}
	
	/** 管理画面 **/
	function getNoSendStepMailList($lim = 15){
		return $this->sendDao->getNoSendStepMailList($lim);
	}
}
?>