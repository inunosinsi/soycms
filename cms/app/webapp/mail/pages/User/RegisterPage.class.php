<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
class RegisterPage extends CommonPartsPage{
	
	function doPost(){
		$register = (object)$_POST["Register"];
		
		//誕生日
		$register->birthday = @mktime(0,0,0,$register->birthday["month"],$register->birthday["day"],$register->birthday["year"]);
		
		//メール
		if(strlen($register->mailAddress) > 0){
			
			$dao = SOY2DAOFactory::create("SOYMailUserDAO");
			$user = SOY2::cast("SOYMailUser",$register);
			
			$this->user = $user;
			
			try{
				$dao->insert($user);
				CMSApplication::jump("User");
			}catch(Exception $e){
				$this->error = true;
			}
		
		}
		
		
		
	}
	
	var $error = false;
	var $user;
	
    function __construct() {
    	WebPage::WebPage();
    	$this->redirectCheck();
    	$this->createTag();
    	
    	$this->createAdd("register_failed","HTMLModel",array(
    		"visible" => $this->error
    	));
    	
    	$this->buildForm();
    }
    
   function buildForm(){
    	
    	SOY2DAOFactory::importEntity("SOYMailUser");
    	$user = ($this->user) ? $this->user : new SOYMailUser();
    	    	
    	$this->createAdd("register_form","HTMLForm");
    	
    	$this->createAdd("mail_address","HTMLInput",array(
    		"name" => "Register[mailAddress]",
    		"value" => $user->getMailAddress(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("name","HTMLInput",array(
    		"name" => "Register[name]",
    		"value" => $user->getName(),
    		"size" => 30
    	));
    	
    	$this->createAdd("furigana","HTMLInput",array(
    		"name" => "Register[reading]",
    		"value" => $user->getReading(),
    		"size" => 30
    	));
    	
    	$this->createAdd("gender_male","HTMLInput",array(
    		"name" => "Register[gender]",
			"value" => 0,
			"id" => "radio_sex_man"
    	));
    	
    	$this->createAdd("gender_female","HTMLInput",array(
    		"name" => "Register[gender]",
			"value" => 1,
			"id" => "radio_sex_woman"
    	));
    	
    	$this->createAdd("birth_year","HTMLInput",array(
    		"name" => "Register[birthday][year]",
    		"value" => ($user->getBirthday()) ? date("Y",$user->getBirthday()) : "",
    		"size" => 5
    	));
    	
    	$this->createAdd("birth_month","HTMLInput",array(
    		"name" => "Register[birthday][month]",
    		"value" => ($user->getBirthday()) ? date("m",$user->getBirthday()) : "",
    		"size" => 3
    	));
    	
    	$this->createAdd("birth_day","HTMLInput",array(
    		"name" => "Register[birthday][day]",
    		"value" => ($user->getBirthday()) ? date("d",$user->getBirthday()) : "",
    		"size" => 3
    	));
    	
    	$this->createAdd("post_number","HTMLInput",array(
    		"name" => "Register[zipCode]",
    		"value" => $user->getZipCode()
    	));
    	
    	$this->createAdd("area","HTMLSelect",array(
    		"name" => "Register[area]",
    		"options" => Area::getAreas(),
    		"value" => $user->getArea()
    	));
    	
    	$this->createAdd("address1","HTMLInput",array(
    		"name" => "Register[address1]",
    		"value" => $user->getAddress1(),
    		"size" => 40
    	));
    	
    	$this->createAdd("address2","HTMLInput",array(
    		"name" => "Register[address2]",
    		"value" => $user->getAddress2(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("tel_number","HTMLInput",array(
    		"name" => "Register[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    		"size" => 30
    	));
    	
    	$this->createAdd("fax_number","HTMLInput",array(
    		"name" => "Register[faxNumber]",
    		"value" => $user->getFaxNumber(),
    		"size" => 30
    	));

    	$this->createAdd("ketai_number","HTMLInput",array(
    		"name" => "Register[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber(),
    		"size" => 30
    	));

    	$this->createAdd("office","HTMLInput",array(
    		"name" => "Register[jobName]",
    		"value" => $user->getJobName(),
    		"style" => "width: 100%;"
    	));

    	$this->createAdd("office_post_number","HTMLInput",array(
    		"name" => "Register[jobZipCode]",
    		"value" => $user->getJobZipCode()
    	));
    	
    	$this->createAdd("jobArea","HTMLSelect",array(
    		"name" => "Register[jobArea]",
    		"options" => Area::getAreas(),
    		"value" => $user->getArea()
    	));

    	$this->createAdd("jobAddress1","HTMLInput",array(
    		"name" => "Register[jobAddress1]",
    		"value" => $user->getJobAddress1(),
    		"size" => 40
    	));

    	$this->createAdd("jobAddress2","HTMLInput",array(
    		"name" => "Register[jobAddress2]",
    		"value" => $user->getJobAddress2(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("office_tel_number","HTMLInput",array(
    		"name" => "Register[jobTelephoneNumber]",
    		"value" => $user->getJobTelephoneNumber(),
    		"size" => 30
    	));

    	$this->createAdd("office_fax_number","HTMLInput",array(
    		"name" => "Register[jobFaxNumber]",
    		"value" => $user->getJobFaxNumber(),
    		"size" => 30
    	));

    	$this->createAdd("memo","HTMLTextArea",array(
    		"name" => "Register[memo]",
    		"text" => $user->getMemo(),
    		"style" => "width: 100%; padding: 2px; margin: 0;"
    	));
    	
    	$this->createAdd("attribute1","HTMLInput",array(
    		"name" => "Register[attribute1]",
    		"text" => $user->getAttribute1(),
    		"style" => "width: 70%;"
    	));
    	
    	$this->createAdd("attribute2","HTMLInput",array(
    		"name" => "Register[attribute2]",
    		"text" => $user->getAttribute2(),
    		"style" => "width: 70%;"
    	));
    	
    	$this->createAdd("attribute3","HTMLInput",array(
    		"name" => "Register[attribute3]",
    		"text" => $user->getAttribute3(),
    		"style" => "width: 70%;"
    	));
    	
    	$this->createAdd("mail_send","HTMLCheckbox",array(
    		"name" => "Register[notSend]",
    		"value" => 1,
    		"selected" => $user->getNotSend(),
    		"elementId" => "checkbox_send_email"
    	));

    }

}
?>