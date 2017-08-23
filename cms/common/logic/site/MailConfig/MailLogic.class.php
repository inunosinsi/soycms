<?php

/**
 * メールの送信を担う
 */
class MailLogic extends SOY2LogicBase{

	private $serverConfig;
	private $send;
	private $receive;

	/**
	 * メール送信準備
	 */
	private function prepare(){
		$serverConfig = $this->serverConfig;

		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP
			&& $serverConfig->getReceiveServerType() != SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP
			){
				throw new Exception("Invalid receive server type.");
			}

			//before smtp
			$this->receive = $serverConfig->buildReceiveMail();
			$this->receive->open();
			$this->receive->close();
		}
	}

	private function prepareSend(){
		$logic = SOY2LogicContainer::get("logic.site.MailConfig.MailConfigLogic");
		$serverConfig = $logic->get();
		$this->serverConfig = $serverConfig;

		if(strlen($this->serverConfig->getFromMailAddress()) <= 0){
			$this->serverConfig->setFromMailAddress("info");
		}

		
		$this->prepare();

		//SOY2Mail
		$this->send = $serverConfig->buildSendMail();
	}

	/**
	 * 送信ロジック
	 */
	public function sendMail($sendTo, $title, $body, $sendToName = ""){

		if(is_null($this->send)){
			$this->prepareSend();
		}
		
		//リセット
		$this->reset();

		//文字コード
		$encoding = $this->serverConfig->getEncoding();
		$this->send->setEncoding($encoding);
		$this->send->setSubjectEncoding($encoding);

		//件名、本文
		$this->send->setSubject($title);
		$this->send->setText($body);

		//送信先
		$this->send->addRecipient($sendTo, $sendToName);

		/*
		//管理者にコピーを送る設定の時
		if($this->serverConfig->isSendWithAdministrator() && $sendTo != $this->serverConfig->getAdministratorMailAddress()){
			$this->send->addBccRecipient($this->serverConfig->getAdministratorMailAddress(), $this->serverConfig->getAdministratorName());
		}
		*/

		$this->send->send();
	}

	/**
	 * テスト送信メール
	 */
	public function sendTestMail($sendTo) {

		if(is_null($this->send)){
			$this->prepareSend();
		}

		$title = "SOY CMS テストメール ".date("Y-m-d H:i:s");
		$content = "これはSOY CMSから送信したテストメールです。";
		$sendToName = "テストメール送信先";

		$this->sendMail($sendTo, $title, $content, $sendToName);
	}

	/**
	 * 件名、本文、受信者、BCC受信者、ヘッダー、添付ファイルをリセット
	 * SOY2MailはsetTextだけではencodedTextが上書きされない
	 */
	private function reset(){
		$this->send->clearSubject();
		$this->send->clearText();
		$this->send->clearRecipients();
		$this->send->clearBccRecipients();
		$this->send->clearHeaders();
		//$this->send->clearAttachments();
	}

	/**
	 * @return boolean
	 */
	public function isValidEmail($email){
		$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
		$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
		$d3     = '\d{1,3}';
		$ip     = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
		$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

		if(! preg_match('/'.$validEmail.'/i', $email) ) {
			return false;
		}
		
		return true;
	}
}
