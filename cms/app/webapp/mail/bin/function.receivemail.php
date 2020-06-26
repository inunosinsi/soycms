<?php
/*
 * Created on 2008/07/29
 *
 * メール受信ジョブ 
 */
function receivemail(){
	$mailDAO = SOY2DAOFactory::create("MailDAO");
	$mail = null;
	
	$mail = null;
	$repeat = true;
	
	SOY2::import("logic.mail.MailLogic");
	$res = MailLogic::receive();
	
	$errorMailDAO = SOY2DAOFactory::create("ErrorMailDAO");
	
	foreach($res as $key => $array){
		$header = $array["header"];
		$from = $header["From"];
		$body = $array["body"];
		
		if(preg_match('/MAILER-DAEMON@/',$from)){
			
			$errorMail = new ErrorMail();
			
			//エラーメール
			if(preg_match("/SOYMAIL: ([0-9]+)_(.*)/",$body,$tmp)){
				$mailid = $tmp[1];
				$errorMail->setMailId($mailid);
			}
			
			//送信先
			if(preg_match_all("/(.*To): (.*)/",$body,$tmp)){
				foreach($tmp[1] as $key => $value){
					if($value == "To"){
						$address= trim($tmp[2][$key]);
						$errorMail->setMailAddress($address);
					}
				}
			}
			
			$errorMail->setMailContent($array["body"]);
			$errorMail->setReceiveDate(time());
			$errorMailDAO->insert($errorMail);
			
		}else{
			echo $header["Subject"];
		}
	}
	
}
?>