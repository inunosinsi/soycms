<?php

class MailLogic extends SOY2LogicBase{

	private $serverConfig;
	private $formConfig;
	private $send;
	private $receive;
	private $replyTo;

	/**
	 * 準備
	 */
	function prepareSend(){

		$serverConfig = $this->serverConfig;

		$this->prepare();

		//SOY2Mail
		$this->send = $serverConfig->createSendServerObject();
		$this->send->setEncoding($serverConfig->getEncoding());
		$this->send->setSubjectEncoding($serverConfig->getEncoding());

		//FROM
		$from = $serverConfig->getAdministratorMailAddress();
    	$label = $serverConfig->getAdministratorName();
		$this->send->setFrom($from,$label);

		//Reply-To
		if(strlen($serverConfig->getReturnMailAddress())>0){
			$this->replyTo = new SOY2Mail_MailAddress($serverConfig->getReturnMailAddress(), $serverConfig->getReturnName(), $serverConfig->getEncoding());
		}

	}

	/**
	 * pop
	 */
	function prepare(){
		$serverConfig = $this->serverConfig;

		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP
			&& $serverConfig->getReceiveServerType() != SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP
			){
				throw new Exception("invalid receive server type");
			}

			//before smtp
			$this->receive = $serverConfig->createReceiveServerObject();
			$this->receive->open();
			$this->receive->close();
		}
	}

	/**
	 * 送信元、返信アドレスの設定
	 * フォーム別の設定で送信元を上書きする
	 */
	function checkFormConfig(SOYInquiry_FormConfig $config){

		//送信元
		if(strlen($config->getFromAddress())>0 && $this->isValidEmail($config->getFromAddress())){
			$this->send->setFrom($config->getFromAddress(),$config->getFromAddressName());
		}

		//Reply-Toを上書き
		if(strlen($config->getReturnAddress())>0 && $this->isValidEmail($config->getReturnAddress())){
			$this->replyTo = new SOY2Mail_MailAddress($config->getReturnAddress(), $config->getReturnAddressName(), $this->serverConfig->getEncoding());
		}
	}

	/**
	 * 一通送信する
	 *
	 * @param String sendTo
	 * @param String title
	 * @param String body
	 * @param String sendToName
	 * @param <String> replyTo
	 * @param Boolean replyToOnly 返信先をユーザのメールアドレスのみにする
	 */
	function sendMail($sendTo,$title,$body,$sendToName,$replyTo = null, $replyToOnly=false){

		//リセット
		$this->reset();

		$replyToArray = array();
		if($replyTo) $replyToArray = $replyTo;
		if($replyToOnly){
			//何もしない
		}else{
			if($this->replyTo) $replyToArray[] = $this->replyTo->getString();
		}

		$this->send->setHeader("Reply-To", implode(",", $replyToArray));

		$this->send->setSubject($title);
		$this->send->setText($body);
		$this->send->addRecipient($sendTo, $sendToName);
		$this->send->send();
		sleep(1);
	}

	/**
	 * 件名、本文、受信者、ヘッダーをリセット
	 * SOY2MailはsetTextだけではencodedTextが上書きされない
	 */
	function reset(){
		$this->send->clearSubject();
		$this->send->clearText();
		$this->send->clearRecipients();
		$this->send->clearHeaders();
	}

	/**
	 * 受信する
	 *
	 * @return array()
	 */
	function receiveMail(){



	}


	function getServerConfig() {
		return $this->serverConfig;
	}
	function setServerConfig($serverConfig) {
		$this->serverConfig = $serverConfig;
	}

	/**
	 * 管理側とユーザ側に通知メールを送信する
	 *
	 * @param Array $userMailAddress ユーザのメールアドレス
	 * @param Array $mailBody 0=>管理者宛のメール本文, 1=>ユーザ宛のメール本文
	 *
	 */
	function sendNotifyMail($columns,$userMailAddress,$mailBody){
		$serverConfig = $this->getServerConfig();
		$formConfig = $this->getFormConfig();

		//準備
		$this->prepareSend();

		//管理者へ
		if($this->formConfig->getIsSendNotifyMail()){
			//送信先
			$sendTo = array();

			//管理者アドレス
			if(strlen($serverConfig->getAdministratorMailAddress())>0){
				$sendTo[] = $serverConfig->getAdministratorMailAddress();
			}

			//フォーム別追加通知メールアドレス
			if(strlen($formConfig->getAdministratorMailAddress())>0){
				$optionMailAddress = explode(",",$formConfig->getAdministratorMailAddress());
				foreach($optionMailAddress as $addr){
					$addr = trim($addr);
					if(strlen($addr)>0){
						$sendTo[] = $addr;
					}
				}
			}

			if(count($sendTo)){
				//送信前にfromとタイトルの値が存在しているかチェック。登録されていなければ、フォーム別の値を取得する
				if(strlen($this->send->getFrom()->getAddress()) === 0){
					$this->checkFormConfig($this->getFormConfig());
				}

				$title = $formConfig->getNotifyMailSubject();
				$title = $this->replaceText($columns,$title);

				$content = htmlspecialchars_decode($mailBody[0], ENT_QUOTES);
				foreach($sendTo as $email){
					try{
						$this->sendMail(
							$email,
							$title,
							$content,
							null,
							($formConfig->getIsReplyToUser() ? $userMailAddress : null ),
							($formConfig->getIsReplyToUser())	//返信先をユーザのメールアドレスのみにする
						);
					}catch(Exception $e){
						//管理者へ送信失敗
						file_put_contents(
							CMS_COMMON."log/inquiry-mail-error-admin.log",
							date("r")." ".$columns[0]->getInquiry()->getTrackingNumber()."\n".
							"From: ".$this->send->getFrom()->getAddress()."\n".
							"To: ".$email."\n".
							"Subject: ".$title."\n".
							$content."\n",
							FILE_APPEND
						);
						if(!file_exists(CMS_COMMON."log/inquiry")){
							mkdir(CMS_COMMON."log/inquiry");
						}
						file_put_contents(
							CMS_COMMON."log/inquiry/mail-".date("YmdHi-").($columns[0]->getInquiry()->getTrackingNumber())."-admin.log",
							var_export($e,true)."\n\n",
							FILE_APPEND
						);
					}
				}
			}
		}

		//ユーザへの通知メール
		if($formConfig->getIsSendConfirmMail()){

			if(count($userMailAddress)>0){

				//フォーム別の送信元、返信アドレスの設定
				$this->checkFormConfig($this->getFormConfig());

				$adminMailAddress = $serverConfig->getAdministratorMailAddress();

				$array = $formConfig->getConfirmMail();

				$title = $array["title"];
				$title = $this->replaceText($columns,$title);

				$content = array();
				if(strlen($array["header"])>0) $content[] = $this->replaceText($columns,$array["header"]);
				if($array["isOutputContent"]>0)$content[] = htmlspecialchars_decode($mailBody[1], ENT_QUOTES);
				if(strlen($array["footer"])>0) $content[] = $this->replaceText($columns,$array["footer"]);
				$content = implode("\r\n", $content);
				foreach($userMailAddress as $email){
					try{
						$this->sendMail(
							$email,
							$title,
							$content,
							null,
							null
						);

					}catch(Exception $e){
						//ユーザへ送信失敗
						file_put_contents(
							CMS_COMMON."log/inquiry-mail-error-user.log",
							date("r")." ".$columns[0]->getInquiry()->getTrackingNumber()."\n".
							"From: ".$this->send->getFrom()->getAddress()."\n".
							"To: ".$email."\n".
							"Subject: ".$title."\n".
							$content."\n",
							FILE_APPEND
						);
						if(!file_exists(CMS_COMMON."log/inquiry")){
							mkdir(CMS_COMMON."log/inquiry");
						}
						file_put_contents(
							CMS_COMMON."log/inquiry/mail-".date("YmdHi-").($columns[0]->getInquiry()->getTrackingNumber())."-user.log",
							var_export($e,true)."\n\n",
							FILE_APPEND
						);
					}
				}
			}

		}
	}

	/**#@+
	 *
	 * @access public
	 */
	function getFormConfig() {
		return $this->formConfig;
	}
	function setFormConfig($formConfig) {
		$this->formConfig = $formConfig;
	}
	function getSend() {
		return $this->send;
	}
	function setSend($send) {
		$this->send = $send;
	}
	function getReceive() {
		return $this->receive;
	}
	function setReceive($receive) {
		$this->receive = $receive;
	}
	/**#@-*/
	private function replaceText($columns,$text){
		//tracking number だけ先に変換
		$confirm = $this->formConfig->getConfirmMail();
		$replace = $confirm['replaceTrackingNumber'];
		if(strlen($replace)>0 && strpos($text, $replace) !== false){
			$text = str_replace($replace,$columns[0]->getInquiry()->getTrackingNumber(),$text);
		}
		foreach($columns as $column){
			$obj = $column->getColumn();
			$replace = $obj->getReplacement();
			if(strlen($replace)>0 && strpos($text, $replace) !== false) $text = str_replace($replace,htmlspecialchars_decode($obj->getMailText(), ENT_QUOTES),$text);
		}
		return $text;
	}

	/**
	 * @return boolean
	 */
	function isValidEmail($email){
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
