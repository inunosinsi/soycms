<?php

class CreateConfirmPage extends WebPage{

	var $mail;
	var $mailDAO;
	var $id;

	function doPost(){

		$mailDAO = SOY2DAOFactory::create("MailDAO");
		$this->mailDAO = $mailDAO;
		$mail = $this->mail;
		
		//送信予約
		if(isset($_POST['wait'])){
			
			//予約送信時刻の値が入力されていない状態で送信予約を行う場合はエラーにする
			if(is_null($mail->getSchedule()) || strlen($mail->getSchedule()) == 0){
				CMSApplication::jump("Mail.CreateConfirm." . $this->id . "?error");
			} 
			
			$mail->setStatus(Mail::STATUS_WAIT);
			$mail = $this->updateMail($mail, true);
			$this->reservationMail($mail);
			CMSApplication::jump("Mail.SendBox");
			exit;
		
		//即時配信
		}elseif(isset($_POST['sendnow'])){
			$mail->setStatus(Mail::STATUS_WAIT);
			$this->updateMail($mail);
			CMSApplication::jump("Mail.SendNow.".$this->id);
			exit;
		
		//戻る
		}elseif(isset($_POST['back'])){
			CMSApplication::jump("Mail.".$this->id);
			exit;
		}
	}

    function CreateConfirmPage($args) {
    	$this->id = (isset($args[0])) ? $args[0] : null;
    	$dao = SOY2DAOFactory::create("MailDAO");
	    $this->mail = $dao->getById($this->id);
		WebPage::WebPage();
		
		$this->createAdd("error", "HTMLModel", array(
			"visible" => (isset($_GET["error"]))
		));

		$this->createAdd("form","HTMLForm");
		$this->createAdd("hidden_value","HTMLInput",array(
			"name" => "hidden_value",
			"value" => base64_encode(serialize($this->mail))
		));

		$this->createAdd("send_count","HTMLLabel",array(
			"text"=>count($this->getMailAddress())."件のメールを送信します。"
		));
		$this->createAdd("mail_title","HTMLLabel",array(
			"text"=> $this->mail->getTitle()
		));
    	$this->createAdd("mail_content","HTMLLabel",array(
    		"text" => $this->mail->getMailContent()
    	));
    	$this->createAdd("mail_schedule","HTMLLabel",array(
    		"text" => $this->mail->getScheduleString()
    	));

		$ignore_users= $this->getMailAddress(true);

		$this->createAdd("ignore_address_list","HTMLLabel",array(
			"text"=>implode("\n",$ignore_users),
			"visible" => count($ignore_users)>0
		));

		if(count($ignore_users)>0){
			$str = "以下の".count($ignore_users)."件のあて先は無効になっているため、除外されます。";
		}else{
			$str = "現在登録されているメールアドレスはすべて有効です。";			
		}
		$this->createAdd("ignore_count","HTMLLabel",array(
			"text"=> $str
		));


	}
	
	/**
	 * 送信メールの更新
	 * @param Mail $mail
	 * @return Mail
	 */
    function updateMail($mail){

    	$mailDAO = $this->mailDAO;

    	if($this->id){
			$mail->setId($this->id);
			$mailDAO->update($mail);
		}else{
			$this->id = ($mailDAO->insert($mail));
		}
		
		return $mail;
    }
    
    /**
     * cronの予約テーブルに送信の予約を登録する
     * @param Mail $mail
     */
    function reservationMail($mail){
    	$reservationDao = SOY2DAOFactory::create("SOYMail_ReservationDAO");
    	
    	$reservation = new SOYMail_Reservation();
    	$reservation->setMailId($mail->getId());
    	$reservation->setOffset(0);
    	$reservation->setScheduleDate($mail->getSchedule());
    	
    	try{
    		$reservationDao->insert($reservation);
    	}catch(Exception $e){
    		var_dump($e);
    		//
    	}
    	
    }

	function getMailAddress($only_disable_user = false){
		$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$dao = $extendLogic->getDAO();
		$query = $dao->getQuery();
		list($query->where,$binds) = $this->mail->getSelectorObject()->generateConditions($only_disable_user);
		
		$checkSOYShop = $extendLogic->checkSOYShopConnect();
		
		if($checkSOYShop===true)$old = SOYMailUtil::switchSOYShopConfig();
		
		$result = $dao->executeQuery($query,$binds);
		
		if($checkSOYShop===true)$old = SOYMailUtil::resetConfig($old);

		$mailaddress = array();

		foreach($result as $user){
			if(strlen($user["name"]) == 0){
				$user["name"] = "名称未設定";
			}

			$mailaddress[] = $user["mail_address"];


		}

		return $mailaddress;
	}
}
?>