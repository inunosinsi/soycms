<?php

class InquiryLogic extends SOY2LogicBase {

	private $mailLogic;
	private $builder;
	private $config;

	private $user;
	private $inqDao;

	function __construct(){
		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$this->mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$this->builder = SOY2Logic::createInstance("logic.mail.MailBuilder");
		$this->config = InquiryOnMypageUtil::getMailConfig();

		SOY2::import("domain.order.SOYShop_Order");

		$this->inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
	}

	function addInquiry(SOYShop_Inquiry $inquiry){
		$inquiry->setTrackingNumber(self::getTrackingNumber());

		$this->inqDao->begin();
		try{
			$id = $this->inqDao->insert($inquiry);
		}catch(Exception $e){
			return null;
		}

		//お問い合わせオブジェクトの再取得
		try{
			$inquiry = $this->inqDao->getById($id);
		}catch(Exception $e){
			return null;
		}

		$mailLogId = self::send($inquiry);
		$inquiry->setMailLogId($mailLogId);

		try{
			$this->inqDao->update($inquiry);
			$this->inqDao->commit();
		}catch(Exception $e){
			var_dump($e);
			return null;
		}

		return $inquiry;
	}

	private function getTrackingNumber(){

		for($i = 0; ; $i++){
			$hash = base_convert(md5($this->user->getId() . time() . $i), 16, 10);
			$trackingnum = substr($hash, 6, 4) . "-" . substr($hash, 10, 4);
			$trackingnum = $this->user->getId() . "-" . $trackingnum;

			try{
				$tmp = $this->inqDao->getByTrackingNumber($trackingnum);
			}catch(Exception $e){
				break;
			}
		}

		return $trackingnum;
	}

	private function send(SOYShop_Inquiry $inquiry){
		$userName = $this->user->getName();
		if(strlen($userName) > 0) $userName .= " 様";

		$content = array();
		$content[] = "お問い合わせ内容";
		$content[] = "-----------------------------------------";
		$content[] = self::printColumn("お問い合わせ番号：", "left", 10) . $inquiry->getTrackingNumber();
		$content[] = self::printColumn("お名前：", "left", 10) . $this->user->getName();
		$content[] = self::printColumn("メールアドレス：", "left", 10) . $this->user->getMailAddress();
		$content[] = self::printColumn("お電話番号：", "left", 10) . $this->user->getTelephoneNumber();
		if(strlen($inquiry->getRequirement())) $content[] = self::printColumn("お問い合わせ種別：", "left", 10) . $inquiry->getRequirement();
		$content[] = "お問い合わせ内容：";
		$content[] = $inquiry->getContent();

		$title = self::buildTitle();
		$body = self::buildBody(implode("\n",$content));

		//MailLogIdを取得
		return $this->mailLogic->sendMail($this->user->getMailAddress(), $title, $body, $userName);
	}

	function printColumn(string $str, string $pos="right", int $width=10){

		$strWidth = mb_strwidth($str);

		if($pos == "right"){
			$size = max(0,$width - $strWidth);
			return str_repeat(" ", $size) . $str;

		} else if ($pos == "center"){
			$size = (int)(max(0,$width - $strWidth) / 2);
			$return = str_repeat(" ",$size);
			return $return . $str . $return;

		} else if ($pos == "left"){
			$size = max(0,$width - $strWidth);
			return $str . str_repeat(" ", $size);
		}

		return $str;
	}

	private function buildTitle(){
		return $this->mailLogic->convertMailContent($this->config["title"], $this->user, new SOYShop_Order());
	}

	private function buildBody($content){

		$mailBody = $this->config["header"] ."\n\n" .
					$content . "\n\n\n".
					$this->config["footer"];

		return $this->mailLogic->convertMailContent($mailBody, $this->user, new SOYShop_Order());
	}

	function setUser($user){
		$this->user = $user;
	}
}
