<?php

class SendMailLogic extends MailLogic{

    /**
	 * 接続
	 */
	function connectSendServer(){}
	
	/**
	 * 接続のクリア
	 */
	function closeSendServer(){}
	
	/**
	 * 一通送信する
	 */
	function sendMail($sendTo,Mail $mail){
		
		$from = $this->serverConfig->getSenderMailAddress();
		$title = $mail->getTitle();
		$body = $mail->getMailContent();
		
		$fromQuery = (strlen($mail->getSenderName())>0) ? '"'.$this->convertTitle($mail->getSenderName()).'" <' . $from . '>' : '<' . $from . '>'; 
		$returnQuery = null;
		if(strlen($mail->getReturnAddress())>0){
			$returnName = $mail->getReturnName();
			$returnAddress = $mail->getReturnAddress();
			$returnQuery = (strlen($returnName)>0) ? '"'.$this->convertTitle($returnName).'" <' . $returnAddress . '>' : '<' . $returnAddress . '>';
		}
		
		
		//値を変換
		$body = str_replace(array("\r\n", "\r"), "\n", $body);  // CRLF, CR -> LF
		$body = preg_replace('/^\\.$/m','..', $body);          // .        -> ..
		$body = str_replace("\n", "\r\n", $body);                // LF       -> CRLF
		$body = mb_convert_encoding($body, "ISO-2022-JP","UTF-8");
		

    	$title = str_replace(array("\r","\n"), "", $title);//改行削除
    	$title = mb_convert_encoding($title, "ISO-2022-JP","UTF-8");
		$title = mb_encode_mimeheader($title);
		
		$headers = array();
		$headers[] = "MIME-Version: 1.0 " ;
		$headers[] = "From: " . $fromQuery;
		$headers[] = "Reply-To: ".$returnQuery;
		$headers[] = "X-SOYMAIL: " . $mail->getId() ."_". md5($title);
		$headers[] = "Date: " . date("r");
		$headers[] = "Content-Type: text/plain; charset=\"ISO-2022-JP\"";
		$headersText = implode("\n",$headers);
		
		$sendmail_params  = "-f".$from;
		
		$result = mail($sendTo, $title, $body, $headersText, $sendmail_params);	
	}
	
	/**
	 * 再接続
	 */
	function reconnectSendServer(){}
	
	/**
	 * 接続
	 */
	function connectReceiveServer(){}
	
	/**
	 * 接続のクリア
	 */
	function closeReceiveServer(){}
	
	/**
	 * 受信する
	 * 
	 * @return array()
	 */
	function receiveMail(){}
	
	/**
	 * 再接続
	 */
	function reconnectReceiveServer(){}
	
}
?>