<?php

class MailDetailPage extends WebPage{

	var $id;
	var $mail;

	function doPost(){
    	if(!$this->id){
			CMSApplication::jump("Mail");
			exit;
    	}
    	$dao = SOY2DAOFactory::create("MailDAO");

	    try{
	    	$mail = $dao->getById($this->id);
	    }catch(Exception $e){
	    	CMSApplication::jump("Mail");
	    }

		$post = $_POST;
		if(isset($post['sendnow'])){
			CMSApplication::jump("Mail.SendNow.".$this->id);
			exit;
		}elseif(isset($post['remove'])){
			CMSApplication::jump("Mail.Remove.".$this->id);
			exit;
		}elseif(isset($post['movetodraft'])){
			CMSApplication::jump("Mail.MoveDraft.".$this->id);
			exit;
		}elseif(isset($post['validate'])){
			if($mail->getStatus() == Mail::STATUS_ERROR){
				$mail->setStatus(Mail::STATUS_WAIT);
				$dao->update($mail);
				CMSApplication::jump("Mail.SendBox");
				exit;
			}
			exit;
		}elseif(isset($post["copy"])){
			if($mail->getStatus() == Mail::STATUS_HISTORY){
				$mail->setStatus(Mail::STATUS_DRAFT);
				$id = $dao->insert($mail);
				CMSApplication::jump("Mail.MailDetail." . $id . "?copy");
			}
		}
		exit;
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? $args[0] : null;

 	  	WebPage::WebPage();

    	$this->createAdd("copy_message","HTMLModel",array(
    		"visible" => (isset($_GET["copy"]))
    	));
		$this->createAdd("form","HTMLForm");


    	//送信待ち、送信済みのメールの詳細を表示
    	if(!$this->id){
			CMSApplication::jump("Mail");
			exit;
    	}
    	$dao = SOY2DAOFactory::create("MailDAO");

	    try{
	    	$mail = $dao->getById($this->id);
	    }catch(Exception $e){
	    	CMSApplication::jump("Mail");
	    }

	    if($mail->getStatus() == Mail::STATUS_DRAFT){
			CMSApplication::jump("Mail.".$this->id);
			exit;
	    }
	    $flag = ($mail->getStatus() == Mail::STATUS_WAIT || $mail->getStatus() == Mail::STATUS_ERROR);
    	$this->createAdd("button_sendnow","HTMLInput",array(
    		"name" => "sendnow",
    		"value" => "すぐに配信",
   			"visible" => $flag
   		));
    	$this->createAdd("button_validate","HTMLInput",array(
    		"name" => "validate",
    		"value" => "再送信予約",
   			"visible" => $mail->getStatus() == Mail::STATUS_ERROR
   		));
   		$this->createAdd("button_validate_wrapper","HTMLModel",array(
   			"visible" => $mail->getStatus() == Mail::STATUS_ERROR
   		));
   		$this->createAdd("button_copy","HTMLInput",array(
    		"name" => "copy",
    		"value" => "コピーして編集",
   			"visible" => $mail->getStatus() == Mail::STATUS_HISTORY
   		));
   		$this->createAdd("button_copy_wrapper","HTMLModel",array(
   			"visible" => $mail->getStatus() == Mail::STATUS_HISTORY
   		));

    	$this->createAdd("button_movetodraft","HTMLInput",array(
    		"name" => "movetodraft",
    		"value" => "再編集",
   			"visible" => $flag
   		));
   		$this->createAdd("button_remove","HTMLInput",array(
    		"name" => "remove",
    		"value" => "削除",
   			"visible" => $flag
   		));

   		$this->createAdd("editable_mail_button","HTMLModel",array(
   			"visible" => $flag
   		));

	    $text = "確認(".$mail->getStatusText().") - " . $mail->getTitle();

    	$this->createAdd("mail_id","HTMLInput",array(
    		"name" => "Mail[id]",
    		"value" => $mail->getId()
    	));

    	$this->createAdd("sendto_detail","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Mail.SendDetail.".$this->id),
    		"visible" => $flag,
    		"text" => $mail->getMailCount() . "件"
    	));

    	$this->createAdd("mode_text","HTMLLabel",array(
    		"text" => $text
    	));

    	$this->createAdd("mail_schedule","HTMLLabel",array(
    		"text" => $mail->getScheduleString()
    	));

    	$this->createAdd("mail_title","HTMLLabel",array(
    		"text" => $mail->getTitle()
    	));

    	$this->createAdd("mail_sender_name","HTMLLabel",array(
    		"text" => $mail->getSenderName()
    	));

    	$this->createAdd("mail_sender_address","HTMLLabel",array(
    		"text" => $mail->getSenderAddress()
    	));

    	$this->createAdd("mail_return_address","HTMLLabel",array(
    		"text" => $mail->getReturnAddress()
    	));

    	$this->createAdd("mail_return_name","HTMLLabel",array(
    		"text" => $mail->getReturnName()
    	));

    	$this->createAdd("mail_content","HTMLLabel",array(
    		"text" => $mail->getMailContent()
    	));

    	/* 以下、セレクタ */
    	$selector = $mail->getSelectorObject();
    	$genderSelector = $selector->getGender();
    	$this->createAdd("selector_gender_male","HTMLLabel",array(
    		"visible" => (isset($genderSelector["male"]) && $genderSelector["male"]),
    		"text" => "男"
    	));
    	$this->createAdd("selector_gender_female","HTMLLabel",array(
    		"visible" => (isset($genderSelector["female"]) && $genderSelector["female"]),
    		"text" => "女"
    	));
    	$this->createAdd("selector_gender_other","HTMLLabel",array(
    		"visible" => (isset($genderSelector["other"]) && $genderSelector["other"]),
    		"text" => "指定なし"
    	));

    	$ages = $selector->getAges();
    	$age = @$ages[$selector->getAge()];
		$this->createAdd("selected_age","HTMLLabel",array(
    		"text" => $age
    	));

    	$areas = Area::getAreas();
    	$area_text = "";
    	foreach($selector->getAreas() as $area){
    		$area_text .= (isset($areas[$area])? $areas[$area] : $area)." ";
    	}
    	$this->createAdd("selected_area","HTMLLabel",array(
    		"text" => $area_text
    	));



    	$attribues = $selector->getAttributes();
    	$this->createAdd("selector_attribute1","HTMLLabel",array(
    		"text" => $attribues[1]
    	));
    	$this->createAdd("selector_attribute2","HTMLLabel",array(
    		"text" => $attribues[2]
    	));
    	$this->createAdd("selector_attribute3","HTMLLabel",array(
    		"text" => $attribues[3]
    	));

		$carrier = $selector->getCarrier();
    	$this->createAdd("selector_carrier_pc","HTMLLabel",array(
    		"visible" => @$carrier["pc"],
    		"text" => "PC"
    	));
    	$this->createAdd("selector_carrier_docomo","HTMLLabel",array(
    		"visible" => @$carrier["docomo"],
    		"text" => "docomo"
    	));
    	$this->createAdd("selector_carrier_au","HTMLLabel",array(
    		"visible" => @$carrier["au"],
    		"text" => "au"
    	));
    	$this->createAdd("selector_carrier_softbank","HTMLLabel",array(
    		"visible" => @$carrier["softbank"],
    		"text" => "Softbank"
    	));
    	$this->createAdd("selector_carrier_willcom","HTMLLabel",array(
    		"visible" => @$carrier["willcom"],
    		"text" => "Willcom"
    	));
    	$this->createAdd("selector_carrier_other","HTMLLabel",array(
    		"visible" => strlen($carrier["other"])>0,
    		"text" => "他:".@$carrier["other"]
    	));

    	$this->createAdd("selected_memo","HTMLLabel",array(
    		"text" => $selector->getMemo()
    	));
    }
}

?>