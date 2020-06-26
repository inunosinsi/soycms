<?php
/**
 * Created on 2008/07/29
 * Update on 2013/08/29
 *
 * メール送信ジョブ
 * @param integer $mailid soymail_mail.id メールテーブルID
 * @param integer $offset 前回までの送信件数
 */
function sendmail($mailid = null, $offset = 0){
	$mailDAO = SOY2DAOFactory::create("MailDAO");
	
	$mail = null;
	$repeat = true;

	$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
	$restrictionCount = (int)$serverConfig->getSendRestriction();
	
	//分割送信かどうか
	$isSplit = ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SPLIT);
	
	while($repeat){
		
		try{
			if($mailid){
				$mail = $mailDAO->getById($mailid);
				$repeat = false;
			}else{
				$mail = $mailDAO->getSendMailForJob();
			}
			
			//送信中に変更する
			$mail->setStatus(Mail::STATUS_SENDING);
			$mail->setSendDate(time());
			
			//送信対象の全件数
			$targetMailTotal = $mail->getSelectorObject()->countAddress();
			$count = $mail->getSelectorObject()->countAddress();
			
			//累積送信件数
			$totalSend = $count;
			
			//ログ表記 今回の送信件数
			if($isSplit && $offset < $targetMailTotal){
				
				//今回で終了ではなく、次回の予約もある場合
				if($targetMailTotal > ($offset + $restrictionCount)){
					$count = $restrictionCount;
					$totalSendCount = $offset + $restrictionCount;
				
				//今回で全件送信完了の場合
				}else{
					$count = $targetMailTotal - $offset;
					$totalSendCount = $targetMailTotal;
				}
			}else{
				$totalSendCount = $count;
			}
			
			echo "sending...($count)\n";
			$mail->setMailCount($totalSendCount);
			
			$mailDAO->update($mail);
		}catch(Exception $e){
			break;
		}
		
		try{
			//メール送信実行
			SOY2::import("logic.mail.MailLogic");
			MailLogic::send($mail, $offset);
			//送信完了に変更する
			$mail->setStatus(Mail::STATUS_HISTORY);
			$mail->setSendedDate(time());
			$mailDAO->update($mail);
		}catch(Exception $e){
			$mail->setStatus(Mail::STATUS_ERROR);
			$mailDAO->update($mail);
			throw $e;
		}
	}
	
	
	
}
?>