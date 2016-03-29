<?php
abstract class MailLogic extends SOY2LogicBase{
	
	protected $serverConfig;
	
	/**
	 * 送信処理
	 * @param Mail $mail
	 * @param integer $offset
	 * @param integer $counter 送信数
	 */
	public static final function send(Mail $mail, $offset = 0){
		
		//timeリミットを増やす
		set_time_limit(0);		
		//サーバ設定の取得
		$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
		
		//popBeforeSMTP
		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != ServerConfig::SERVER_TYPE_SMTP
			&& $serverConfig->getReceiveServerType() != ServerConfig::SERVER_TYPE_IMAP
			){
				throw new Exception("invalid receive server type");
			}

			//before smtp
			$receive = $serverConfig->createReceiveServerObject();
			$receive->open();
			$receive->close();
		}


		$selector = $mail->getSelectorObject();
		$selectorCount = count($selector->searchSendTo());	//送信数を取得

		//送信側の設定
    	$title = $mail->getTitle(); 
		$send = $serverConfig->createSendServerObject();
		$send->setEncoding("ISO-2022-JP");
		$send->setSubjectEncoding("ISO-2022-JP");
		$send->setFrom($mail->getSenderAddress(),$mail->getSenderName());
		$sender = new SOY2Mail_MailAddress($mail->getSenderAddress(),$mail->getSenderName(),$send->getEncoding());
		$returnpath = new SOY2Mail_MailAddress($mail->getReturnAddress(),$mail->getReturnName(),$send->getEncoding());
		$send->setHeader("Return-Path", $sender);
		$send->setHeader("Reply-To", $returnpath);
		$send->setHeader("X-SOYMail",$mail->getId() ."_". md5($title));
		$send->setHeader("Date",date("r"));
		
		
		$send->setSubject($title);
//		$send->setText($mail->getMailContent());

		//
		$connectorDao = SOY2DAOFactory::create("SOYMail_SOYShopConnectorDAO");
		try{
			$config = $connectorDao->get()->getConfig();
		}catch(Exception $e){
			$config = array();
		}
		
		//SOYShopと連携しているか？
		$connect = (isset($config["siteId"]) && $config["siteId"] > 0);
		
		//userDao
		$extendUserDAOLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$userDao = $extendUserDAOLogic->getDAO();

		$counter = 0;
		
		//分割配信するか
		$isSplit = ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SPLIT);
		
		//一度に送信する宛先の上限
		$restrictionCount = (int)$serverConfig->getSendRestriction();
		
		$reservationFlag = false;//次回予約作成フラグ

		while(true){
			
			//一度に送信する上限を超えた場合は次回送信予約の手続きを開始する
			if($isSplit && $restrictionCount > 0 && $counter === $restrictionCount){
				$reservationFlag = true;
//				$offset = $restrictionCount;
				break;
			} 
			
			$user = $selector->getNextSendAddress($offset);
			if(!$user) break;
			$send->setRecipients(array());
			$send->clearText();
			$send->setText(MailLogic::convertMailContent($mail->getMailContent(),$user));
			$send->addRecipient($user->getMailAddress(),$user->getName());
			try{
				$send->send();
			}catch(Exception $e){
				//エラーレポート
				self::saveErrorLog($user, $connect, $userDao);
			}
			
			$counter++;
		}
		
		//次回送信予約(cron)
		if($isSplit && $reservationFlag === true){
			$interval = (int)$serverConfig->getSendRestrictionInterval();
			if($interval === 0) $interval = 30;
			self::insertReservation($mail->getId(), $selectorCount, $restrictionCount, $interval, $offset);
		}		
	}
	
	//次の予約送信がある時は、予約テーブルに挿入する
	function insertReservation($mailId, $selectorCount, $restrictionCount, $interval, $offset = 0){
				
		//予約送信時刻の設定
		$nextReservationDate = time() + $interval * 60;
			
		//次回のメール送信開始
		$nextOffset = $offset + $restrictionCount;
		
		if($selectorCount > $nextOffset){
			$reservationDao = SOY2DAOFactory::create("SOYMail_ReservationDAO");
	    	
	    	$reservation = new SOYMail_Reservation();
	    	$reservation->setMailId($mailId);
	    	$reservation->setOffset($nextOffset);
	    	$reservation->setScheduleDate($nextReservationDate);
	    	
	    	try{
	    		$reservationDao->insert($reservation);
	    	}catch(Exception $e){
	    		//
	    	}
		}
	}
	
	/**
	 * 送信エラーの際に、エラーしたメールアドレスのログと、送信できなかったユーザのエラーフラグを追加
	 * @param SOYMailUser $user
	 * @param boolean $connect SOY Shopと連携しているか
	 * @param SOYMailUserDAO $userDao
	 */
	function saveErrorLog($user, $connect, $userDao){
		
		//ログを保管
		self::saveLog($user->getMailAddress());
		
		//SOYShopと連携していない場合
		if(!$connect){
			$user->setIsError(SOYMailUser::USER_IS_ERROR);
//			$user->setNotSend(SOYMailUser::USER_NOT_SEND);
							
		//SOYShopと連携している場合
		}else{
			$user->setIsError(SOYShop_User::USER_IS_ERROR);
//			$user->setNotSend(SOYShop_User::USER_NOT_SEND);
		}
		
		try{
			$userDao->update($user);
		}catch(Exception $e){
			//
		}
	}
	
	function saveLog($text){
		$dir = SOYMAIL_BIN_DIR . "/log";
		
		if(!is_dir($dir)) mkdir($dir);
		
		//改行を入れる
		$text .= "\n";
		
		$file = $dir . "/" . date("Ymd", time()) . "-error.log";
		file_put_contents($file, var_export($text, true), FILE_APPEND);
	}

	function debug($string){
		//echo htmlspecialchars($string) . "<br>";
	}
	
	function info($string){
		//echo htmlspecialchars($string) . "<br>";
	}
	
	function error($string){
		$this->info($string);
	}
	//TODO SOY2Mailにしないと
	/**
	 * エラーメール受信処理
	 */
	public static final function receive(){
		$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
		$reciveLogic = MailLogic::getReceiveServerLogic($serverConfig);
		$reciveLogic->connectReceiveServer();
		$res = $reciveLogic->receiveMail();		
		$reciveLogic->closeReceiveServer();
		
		return $res;
	}
	//もうどこからも呼ばれていないはず。これでSendMailLogicとSMTPMailLogic、IMAPMailLogicはいらない？
	/**
	 * @return logic
	 */
	public static final function getSendServerLogic($serverConfig){
		$serverType = $serverConfig->getSendServerType();
		
		switch($serverType){
			
			case ServerConfig::SERVER_TYPE_SMTP:
				$logic = SOY2Logic::createInstance("logic.mail.SMTPMailLogic");
				break;
			case ServerConfig::SERVER_TYPE_IMAP:
				$logic = SOY2Logic::createInstance("logic.mail.IMAPMailLogic");
				break;
			case ServerConfig::SERVER_TYPE_SENDMAIL:
				$logic = SOY2Logic::createInstance("logic.mail.SendMailLogic");
				break;
			
		}
		
		$logic->serverConfig = $serverConfig;
		
		return $logic;
	}
	
	/**
	 * connectReciveServer
	 * @return logic
	 */
	public static final function getReceiveServerLogic($serverConfig){
		$serverType = $serverConfig->getReceiveServerType();
		
		switch($serverType){
			
			case ServerConfig::SERVER_TYPE_SMTP:
				$logic = SOY2Logic::createInstance("logic.mail.SMTPMailLogic");
				break;
			case ServerConfig::SERVER_TYPE_IMAP:
				$logic = SOY2Logic::createInstance("logic.mail.IMAPMailLogic");
				break;
			case ServerConfig::SERVER_TYPE_SENDMAIL:
				$logic = SOY2Logic::createInstance("logic.mail.SendMailLogic");
				break;
			
		}
		
		$logic->serverConfig = $serverConfig;
		
		return $logic;
	}
	
	/* 以下 abstract メソッド */
	
	/**
	 * 接続
	 */
	abstract function connectSendServer();
	
	/**
	 * 接続のクリア
	 */
	abstract function closeSendServer();
	
	/**
	 * 一通送信する
	 */
	abstract function sendMail($sendTo,Mail $mail);
	
	/**
	 * 再接続
	 */
	abstract function reconnectSendServer();
	
	/**
	 * 接続
	 */
	abstract function connectReceiveServer();
	
	/**
	 * 接続のクリア
	 */
	abstract function closeReceiveServer();
	
	/**
	 * 受信する
	 * 
	 * @return array()
	 */
	abstract function receiveMail();
	
	/**
	 * 再接続
	 */
	abstract function reconnectReceiveServer();
	
	/**
	 * 置換を行います。
	 */
	public static function convertMailContent($content,$user){
				
		$content = str_replace("#NAME#",$user->getName(),$content);
		$content = str_replace("#READING#",$user->getReading(),$content);
		$content = str_replace("#MAILADDRESS#",$user->getMailAddress(),$content);
		$content = str_replace("#BIRTH_YEAR#",@date("Y",$user->getBirthday()),$content);
		$content = str_replace("#BIRTH_MONTH#",@date("t",$user->getBirthday()),$content);
		$content = str_replace("#BIRTH_DAY#",@date("j",$user->getBirthday()),$content);
		
		return $content;
	}



}
?>
