<?php

class SMTPMailLogic extends MailLogic{
	
	private $smtpConnect = null;
	private $popConnect = null;
	
	/* MailLogicのメソッド */
	
	/**
	 * 接続
	 */
	function connectSendServer(){
		
		$addr = $this->serverConfig->getSendServerAddress();
		if($this->serverConfig->getIsUseSSLSendServer()){
			$addr = "ssl://" . $addr;
		}
		
		$this->smtpConnect = fsockopen($addr, $this->serverConfig->getSendServerPort(), $errno, $errstr);
				
		if(!$this->smtpConnect){
			$this->closeSendServer();
			throw new Exception("faild to connet");
		}

		//hello
		$this->smtpCommand("EHLO ".$this->serverConfig->getSendServerAddress());
		$buff = $this->getSmtpResponse();
		
		if(substr($buff,0,3) != "220"){
			throw new Exception("Failed:EHLO");
		}
		
		//SMTPAuth
		if($this->serverConfig->getIsUseSMTPAuth()){		
			
			//AUTHが可能か確認する
			$authType = false;
			 
			while(true){
				$str = $this->getSmtpResponse();
				if(preg_match("/250-AUTH/i",$str)){
					if(preg_match("/PLAIN/i",$str)){
						$authType = "PLAIN";
					}else if(preg_match("/LOGIN/i",$str)){
						$authType = "LOGIN";
					}
					
					break;
					
				}
				if(preg_match("/8BITMIME/i",$str))break;
			}
			
			if($authType === false){
				throw new Exception("Invalid SMTP Auth Type");
			}
			
			switch($authType){
				case "PLAIN":
					
					$this->smtpCommand("AUTH PLAIN ".base64_encode(
						$this->serverConfig->getSendServerUser() . "\0" .
						$this->serverConfig->getSendServerUser() . "\0" .
						$this->serverConfig->getSendServerPassword()));
					
					while(true){
						$str = $this->getSmtpResponse();
						if(preg_match("/235/i",$str)) break;
						if(preg_match("/501/i",$str)) throw new Exception("smtp login failed");
					}
					
					break;
				
				case "LOGIN":
					$this->smtpCommand("auth login");
					$str = $this->getSmtpResponse();
					if(!preg_match("/334/i",$str)) throw new Exception("smtp login failed");
					$this->smtpCommand(base64_encode($this->serverConfig->getSendServerUser()));
					$this->smtpCommand(base64_encode($this->serverConfig->getSendServerPassword()));
					
					while(true){
						$str = $this->getSmtpResponse();
						if(preg_match("/235/i"))break;
						if(preg_match("/535/i",$str))throw new Exception("smtp login failed");
					}
					
					break;
			}
		}
		
		
	}
	
	/**
	 * 接続のクリア
	 */
	function closeSendServer(){
		if($this->smtpConnect && $this->smtpCommand("QUIT")){
			fclose($this->smtpConnect);
		}
		
		$this->smtpConnect = null;
	}
	
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

    	$title = str_replace(array("\r","\n"), "", $title);//改行削除
    	$title = $this->convertTitle($title);
		
		$this->smtpCommand("MAIL FROM:<".$from.">");
		
		while(true){
			$str = $this->getSmtpResponse();
			if(preg_match("/Ok/i",$str)) break;
			if(substr($str,0,3)!="250")throw new Exception("Failed: MAIL FROM");
		}
				
		$this->smtpCommand("RCPT TO:<$sendTo>");
		while(true){
			$str = $this->getSmtpResponse();
			if(preg_match("/Ok/i",$str)) break;
			if(substr($str,0,3)!="250")throw new Exception("Failed: RCPT TO");
		}
		
		$this->smtpCommand("DATA");
		while(true){
			$str = $this->getSmtpResponse();
			if(preg_match("/354/i",$str)) break;
			if(substr($str,0,3)!="250")throw new Exception("Failed: DATA");
		}
			
		if($returnQuery){
			$this->data("Reply-To: ".$returnQuery);
		}
		$this->data("X-SOYMAIL: " . $mail->getId() ."_". md5($title));
		$this->data("Date: " . date("r"));
		$this->data("Subject: ".$title);
		$this->data("From: $fromQuery");
		$this->data("To: $sendTo");
		$this->data("Content-Type: text/plain; charset=\"ISO-2022-JP\"");
		$this->data("");
		$this->data("$body");
		
		$this->smtpCommand(".");
	}
	
	/**
	 * 再接続
	 */
	function reconnectSendServer(){
		$this->closeSendServer();
		$this->connectSendServer();
	}
	
	/**
	 * 接続
	 */
	function connectReceiveServer(){
		$this->popConnect = fsockopen($this->serverConfig->getReceiveServerAddress(), $this->serverConfig->getReceiveServerPort(), $errno, $errstr);
		
		if(!$this->popConnect){
			$this->disconnect();
			throw new Exception("faild to connet");
		}
		
		$buff = $this->popCommand("USER ".$this->serverConfig->getReceiveServerUser());
		if(!$buff)throw new Exception("Failed to connect pop server");
		$buff = $this->popCommand("PASS ".$this->serverConfig->getReceiveServerPassword());
		if(!$buff)throw new Exception("Failed to connect pop server");
		
	}
	
	/**
	 * 接続のクリア
	 */
	function closeReceiveServer(){
		$this->popCommand("QUIT");
		fclose($this->popConnect);
	}
	
	/**
	 * 受信する
	 * 
	 * @return array()
	 */
	function receiveMail(){
		
		$res = $this->popCommand("LIST");
		if(!$res)throw new Exception("failed to open Receive Server");
		
		$counter = 0;
		
		$mails = array();
		while(true){
			$buff = $this->getPopResponse();
			if($buff == ".")break;
			
			$array = explode(" ",$buff);
			
			if(!is_numeric($array[0]))continue;
			
			$mails[$array[0]] = array(
				"size" => (int)$array[1]
			);
		}
		
		foreach($mails as $id => $array){
			$res = $this->popCommand("RETR ".$id);
			if(!$res)continue;
			
			$flag = false;
			$header = array();
			$body = "";
			$encoding = "JIS";
			
			while(true){
				$buff = $this->getPopResponse();
				if($buff == ".")break;
				
				if(strlen($buff)==0){
					$flag = true;
					
					if(isset($header["Content-Type"]) && preg_match("/charset=(.*)/",$header["Content-Type"],$tmp)){
						$encoding = $tmp[1];
					}
					
					continue;
				}
				
				if($flag){
					$body .= "\r\n" . $buff;
				}else{
					$buff = explode(":",$buff);
					if(count($buff)>1)$header[$buff[0]] = trim($buff[1]);
				}										
			}
			
			$mails[$id]["title"] = mb_decode_mimeheader(@$header["Subject"]);
			$mails[$id]["header"] = $header;
			$mails[$id]["body"] = mb_convert_encoding($body,"UTF-8",$encoding);
			
			//メールを削除する
			$this->popCommand("DELE " . $id);
		}
		
		
		return $mails;
		
		
	}
	
	/**
	 * 再接続
	 */
	function reconnectReceiveServer(){
		$this->closeReceiveServer();
		$this->connectReceiveServer();
	}
	
	/* 以下、内部使用のメソッド */
	function popCommand($string){
    	
    	fputs($this->popConnect, $string."\r\n");
  		$this->debug($string);
  		
  		$buff = fgets($this->popConnect);
  		
  		if(strpos($buff,"+OK") == 0){
  			return $buff;
  		}else{
  			return false;
  		}
    }
    
    function getPopResponse(){
    	$buff = fgets($this->popConnect);
    	$buff = rtrim($buff, "\r\n");
    	
    	$this->debug($buff);
    		
    	return $buff;
    }
    
    function smtpCommand($string){
 		
 		if(!$this->smtpConnect){
			throw new Exception('SMTP is null');
 			return;
 		}
 		
 		$result = fputs($this->smtpConnect, $string."\r\n");
 		
 		if($result == false){
			throw new Exception('Result is false.');
 		}
 		
 		$this->debug($string);
    }
    
    function getSmtpResponse(){
    	$buff = fgets($this->smtpConnect);
    	$this->debug($buff);
    	return $buff;
    }
    
    function data($data){
    	$data = mb_convert_encoding($data,"JIS","UTF-8");
    	
    	$result = fputs($this->smtpConnect, $data."\r\n");
    	
    	if($result == false){
			throw new Exception('send data. Result is false.');
    	}
    }
    
    function convertTitle($title){
    	$encoding = "UTF-8";
    	
		if(strlen($title)){
			return mb_encode_mimeheader($title,$encoding);
		}else{
			return $title;
		}
    }
    
}


?>