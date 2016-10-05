<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
class IndexPage extends CommonPartsPage{
	
	var $id;
	var $mail;
	
	function doPost(){
		$mailDAO = SOY2DAOFactory::create("MailDAO");
		
		
		if(isset($_POST["hidden_value"])){
			
			$mail = unserialize(base64_decode($_POST["hidden_value"]));
			$selector = $mail->getSelectorObject();
			
		}else{
		
			$mail =  SOY2::Cast("Mail",(object)@$_POST["Mail"]);
			
			$selector = SOY2::Cast("MailSelector",(object)@$_POST["Selector"]);
			$config =  SOY2::Cast("MailConfig",(object)@$_POST["Config"]);
			
			$mail->setCreateDate(time());
			$mail->setSelectorObject($selector);
			$mail->setConfigureObject($config);
		}
				
		$mail->setMailCount($selector->countAddress());
		
		if(isset($_POST["save_draft"]) || isset($_POST['send'])){
			$mail->setStatus(Mail::STATUS_DRAFT);
			if(strlen($mail->getTitle())<1){
				$mail->setTitle("(non title)");
			}
			
			try{
				if($this->id){
					$mail->setId($this->id);
					$mailDAO->update($mail);
				}else{	
					$this->id = ($mailDAO->insert($mail));
					$mail->setId($this->id);
				}
			}catch(Exception $e){
				var_dump($e);
				exit;
			}
			
			if(isset($_POST['send'])){
				CMSApplication::jump("Mail.CreateConfirm.".$this->id);
				exit;
			}			
			CMSApplication::jump("Mail.".$this->id);
		}
		
		$this->mail = $mail;
		$this->id = $mail->getId();
		
	}
	
    function __construct($args) {
    	$this->id = (isset($args[0])) ? $args[0] : null;
    	
    	WebPage::__construct();
    	
    	$this->createTag();
    	
    	
    	$this->buildForm();
    }
    
    function buildForm(){
    	
    	$this->createAdd("send_form","HTMLForm");
    	
    	$dao = SOY2DAOFactory::create("MailDAO");
    	$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
    	
    	if($this->mail){
    		$mail = $this->mail;
	    	$text = "編集 - " . $mail->getTitle();
	    	
    	//既存のメール編集の場合
    	}else if($this->id){
	    	$mail = ($this->mail) ? $this->mail : $dao->getById($this->id);
	    	$text = "編集 - " . $mail->getTitle();
			if($mail->getStatus() != Mail::STATUS_DRAFT){
				CMSApplication::jump("Mail.MailDetail.".$this->id);
			}
    	}else{
    		$mail = new Mail();
    		
    		//サーバ設定から初期設定を取り込む
	    	$mail->setSenderName($serverConfig->getAdministratorName());
	    	$mail->setSenderAddress($serverConfig->getSenderMailAddress());
	    	$mail->setReturnAddress($serverConfig->getReturnMailAddress());
	    	$mail->setReturnName($serverConfig->getReturnName());
	    	$mail->setMailContent("\r\n\r\n\r\n" . $serverConfig->getSignature());
	    	
	    	$text = "作成";
    	}
    	
    	$this->createAdd("mail_id","HTMLInput",array(
    		"name" => "Mail[id]",
    		"value" => $mail->getId()
    	));

    	$this->createAdd("mode_text","HTMLLabel",array(
    		"text" => $text
    	));
    	
    	$this->createAdd("mail_schedule","HTMLInput",array(
    		"name" => "Mail[schedule]",
    		"value" => $mail->getScheduleString()
    	));
    	
    	$this->createAdd("mail_title","HTMLInput",array(
    		"name" => "Mail[title]",
    		"value" => $mail->getTitle()
    	));
    	
    	$this->createAdd("mail_sender_name","HTMLInput",array(
    		"name" => "Mail[senderName]",
    		"value" => $mail->getSenderName()
    	));
    	
    	$this->createAdd("mail_sender_address","HTMLInput",array(
    		"name" => "Mail[senderAddress]",
    		"value" => $mail->getSenderAddress()
    	));
    	
    	$this->createAdd("mail_return_address","HTMLInput",array(
    		"name" => "Mail[returnAddress]",
    		"value" => $mail->getReturnAddress()
    	));
    	
    	$this->createAdd("mail_return_name","HTMLInput",array(
    		"name" => "Mail[returnName]",
    		"value" => $mail->getReturnName()
    	));
    	
    	$this->createAdd("mail_content","HTMLTextArea",array(
    		"name" => "Mail[mailContent]",
    		"value" => $mail->getMailContent()
    	));
    	
    	/* 以下、設定関連 */
    	
    	$config = $mail->getConfigureObject();
    	$this->createAdd("config_is_html","HTMLCheckbox",array(
    		"name" => "Config[isHTMLMail]",
    		"selected" => $config->getIsHTMLMail()
    	));
    	
    	$this->createAdd("config_speed_adjust","HTMLSelect",array(
    		"name" => "Config[speedAdjustment]",
    		"selected" => $config->getSpeedAdjustment(),
    		"options" => array(
    		
    		)
    	));
    	
    	/* 以下、セレクタ */
    	$selector = $mail->getSelectorObject();
    	$genderSelector = $selector->getGender();
    	$this->createAdd("selector_gender_male","HTMLCheckbox",array(
    		"name" => "Selector[gender][male]",
    		"value" => 1,
    		"selected" => (isset($genderSelector["male"]) && $genderSelector["male"]),
    		"label" => "男"
    	));
    	$this->createAdd("selector_gender_female","HTMLCheckbox",array(
    		"name" => "Selector[gender][female]",
    		"value" => 1,
    		"selected" => (isset($genderSelector["female"]) && $genderSelector["female"]),
    		"label" => "女"
    	));
    	$this->createAdd("selector_gender_other","HTMLCheckbox",array(
    		"name" => "Selector[gender][other]",
    		"value" => 1,
    		"selected" => (isset($genderSelector["other"]) && $genderSelector["other"]),
    		"label" => "指定なし"
    	));
    	
    	$this->createAdd("selector_age","HTMLSelect",array(
    		"name" => "Selector[age]",
    		"options" => $selector->getAges(),
    		"selected" => $selector->getAge()
    	));
    	
    	$this->createAdd("selector_area","HTMLSelect",array(
    		"name" => "Selector[areas][]",
			"options" => Area::getAreas(),
			"selected" => $selector->getAreas()
    	));
    	
    	//SOY Shopの時のみ誕生日検索
    	DisplayPlugin::toggle("display_birthday_form", SOY2Logic::createInstance("logic.user.ExtendUserDAO")->checkSOYShopConnect());
    	
    	$birthday = (is_array($selector->getBirthday())) ? $selector->getBirthday() : array();
    	$this->createAdd("selector_birth_year","HTMLInput",array(
    		"name" => "Selector[birthday][year]",
    		"value" => (isset($birthday["year"])) ? $birthday["year"] : "",
    		"size" => 5
    	));
    	
    	$this->createAdd("selector_birth_month","HTMLInput",array(
    		"name" => "Selector[birthday][month]",
    		"value" => (isset($birthday["month"])) ? $birthday["month"] : "",
    		"size" => 3
    	));
    	
    	$this->createAdd("selector_birth_day","HTMLInput",array(
    		"name" => "Selector[birthday][day]",
    		"value" => (isset($birthday["day"])) ? $birthday["day"] : "",
    		"size" => 3
    	));
    	
    	$attribues = $selector->getAttributes();
    	$this->createAdd("selector_attribute1","HTMLInput",array(
    		"name" => "Selector[attributes][1]",
    		"value" => $attribues[1]
    	));
    	$this->createAdd("selector_attribute2","HTMLInput",array(
    		"name" => "Selector[attributes][2]",
    		"value" => $attribues[2]
    	));	
    	$this->createAdd("selector_attribute3","HTMLInput",array(
    		"name" => "Selector[attributes][3]",
    		"value" => $attribues[3]
    	));	

		$carrier = $selector->getCarrier();
    	$this->createAdd("selector_carrier_pc","HTMLCheckbox",array(
    		"name" => "Selector[carrier][pc]",
    		"value" => 1,
    		"selected" => @$carrier["pc"],
    		"label" => "PC"
    	));	
    	$this->createAdd("selector_carrier_docomo","HTMLCheckbox",array(
    		"name" => "Selector[carrier][docomo]",
    		"value" => 1,
    		"selected" => @$carrier["docomo"],
    		"label" => "docomo"
    	));	
    	$this->createAdd("selector_carrier_au","HTMLCheckbox",array(
    		"name" => "Selector[carrier][au]",
    		"value" => 1,
    		"selected" => @$carrier["au"],
    		"label" => "au"
    	));	
    	$this->createAdd("selector_carrier_softbank","HTMLCheckbox",array(
    		"name" => "Selector[carrier][softbank]",
    		"value" => 1,
    		"selected" => @$carrier["softbank"],
    		"label" => "Softbank"
    	));	
    	$this->createAdd("selector_carrier_willcom","HTMLCheckbox",array(
    		"name" => "Selector[carrier][willcom]",
    		"value" => 1,
    		"selected" => @$carrier["willcom"],
    		"label" => "Willcom"
    	));	
    	$this->createAdd("selector_carrier_other","HTMLInput",array(
    		"name" => "Selector[carrier][other]",
    		"value" => @$carrier["other"] 
    	));	
    	
    	$this->createAdd("selector_memo","HTMLInput",array(
    		"name" => "Selector[memo]",
    		"value" => $selector->getMemo() 
    	));	
    }
}
?>