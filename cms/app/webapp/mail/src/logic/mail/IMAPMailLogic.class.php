<?php
class IMAPMailLogic extends MailLogic{
	
	private $con;
	
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
	function sendMail($sendTo,Mail $mail){}
	
	/**
	 * 再接続
	 */
	function reconnectSendServer(){}
	
	/**
	 * 接続
	 */
	function connectReceiveServer(){
		$this->con = imap_open("{".$this->serverConfig->getReceiveServerAddress().":".$this->serverConfig->getReceiveServerPort()."}",
			$this->serverConfig->getReceiveServerUser(),
			$this->serverConfig->getReceiveServerPassword()
		);
		
		if($this->con === false){
			throw new Exception("Failed");
		}		
	}
	
	/**
	 * 接続のクリア
	 */
	function closeReceiveServer(){
		imap_close($this->con);
	}
	
	/**
	 * 受信する
	 * 
	 * @return array()
	 */
	function receiveMail(){
		$unseen = imap_search($this->con, "UNSEEN");
		
		$res = array();
		
		if($unseen == false){
			return $res;
		}
		
		foreach($unseen as $key => $i){
			$head = imap_header($this->con, $i);
			$body = imap_body($this->con, $i, FT_INTERNAL);
			$title = mb_decode_mimeheader(@$head->subject);
			
			$res[] = array(
				"title" => $title,
				"head" => $head,
				"body" => $body
			);
			
			echo imap_setflag_full($this->con, $i, "\\Seen") . "<br>"; 
		}
		
		return $res;

	}
	
	/**
	 * 再接続
	 */
	function reconnectReceiveServer(){
		$this->closeReceiveServer();
		$this->connectReceiveServer();
	}
}
?>
