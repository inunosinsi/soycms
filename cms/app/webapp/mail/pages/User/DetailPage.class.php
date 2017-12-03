<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
class DetailPage extends CommonPartsPage{

    function __construct($args) {
    	parent::__construct();
    	
    	$this->createTag();

    	$id = (isset($args[0])) ? (int)$args[0] : null;
    	$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		
    	$mailUser = $extendLogic->getByUserId($id);

    	$this->buildForm($mailUser);
    }

	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Detail"])){
			$detail = (object)$_POST["Detail"];
			
			//誕生日
			$detail->birthday = @mktime(0,0,0,$detail->birthday["month"],$detail->birthday["day"],$detail->birthday["year"]);
	
			//メール
			if(strlen($detail->mailAddress) > 0){
				$dao = SOY2DAOFactory::create("SOYMailUserDAO");
				
				try{
					//元のデータを読み込む：readonlyな値をからの値で上書きしないように
					$user = $dao->getById($detail->id);
					$user = SOY2::cast($user, $detail);
					
					$notSend = (isset($_POST["Detail"]["notSend"]) && $_POST["Detail"]["notSend"] > 0) ? 1 : 0;
					$user->setNotSend($notSend);
					
					$dao->update($user);
				}catch(Exception $e){
					//
				}
			}
			
			CMSApplication::jump("User.Detail.".$detail->id);
		}
	}
	
   function buildForm($user){
    	
    	$this->createAdd("detail_form","HTMLForm");
    	
    	$this->createAdd("id","HTMLInput",array(
    		"name" => "Detail[id]",
    		"value" => $user->getId(),
    		"readOnly" => "readOnly"
    	));

    	$this->createAdd("mail_address","HTMLInput",array(
    		"name" => "Detail[mailAddress]",
    		"value" => $user->getMailAddress(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("name","HTMLInput",array(
    		"name" => "Detail[name]",
    		"value" => $user->getName(),
    		"size" => 30
    	));
    	
    	$this->createAdd("furigana","HTMLInput",array(
    		"name" => "Detail[reading]",
    		"value" => $user->getReading(),
    		"size" => 30
    	));

    	$this->createAdd("gender_male","HTMLCheckbox", array(
    		"type" => "radio",
			"name" => "Detail[gender]",
			"value" => 0,
			"elementId" => "radio_sex_male",
			"selected" => ($user->getGender() === 0 OR $user->getGender() === "0") ? true : false 
    	));
    	
    	$this->createAdd("gender_female","HTMLCheckbox", array(
    		"type" => "radio",
    		"name" => "Detail[gender]",
			"value" => 1,
			"elementId" => "radio_sex_female",
			"selected" => ($user->getGender() === 1 OR $user->getGender() === "1") ? true : false 
    	));
    	
    	
    	$birthday = null;
   		if(is_numeric($user->getBirthday())){
    		$birthday = $user->getBirthday();
    	//SOYShopの場合
    	}else{
    		if(!is_null($user->getBirthday())){
    			$birthArray = explode("-", $user->getBirthday());
	    		$birthday = mktime(0,0,0,$birthArray[1], $birthArray[2], $birthArray[0]);
    		}
    	}
    	$this->createAdd("birth_year","HTMLInput",array(
    		"name" => "Detail[birthday][year]",
    		"value" => ($birthday) ? date("Y",$birthday) : "",
    		"style" => "width:30% !important"
    	));
    	
    	$this->createAdd("birth_month","HTMLInput",array(
    		"name" => "Detail[birthday][month]",
    		"value" => ($birthday) ? date("m",$birthday) : "",
    		"style" => "width:30% !important"
    	));
    	
    	$this->createAdd("birth_day","HTMLInput",array(
    		"name" => "Detail[birthday][day]",
    		"value" => ($birthday) ? date("d",$birthday) : "",
    		"style" => "width:30% !important"
    	));
    	
    	$this->createAdd("post_number","HTMLInput",array(
    		"name" => "Detail[zipCode]",
    		"value" => $user->getZipCode()
    	));
    	
    	$this->createAdd("area","HTMLSelect",array(
    		"name" => "Detail[area]",
    		"options" => Area::getAreas(),
    		"selected" => $user->getArea()
    	));
    	
    	$this->createAdd("address1","HTMLInput",array(
    		"name" => "Detail[address1]",
    		"value" => $user->getAddress1(),
    		"size" => 40
    	));
    	
    	$this->createAdd("address2","HTMLInput",array(
    		"name" => "Detail[address2]",
    		"value" => $user->getAddress2(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("tel_number","HTMLInput",array(
    		"name" => "Detail[telephoneNumber]",
    		"value" => $user->getTelephoneNumber(),
    		"size" => 30
    	));
    	
    	$this->createAdd("fax_number","HTMLInput",array(
    		"name" => "Detail[faxNumber]",
    		"value" => $user->getFaxNumber(),
    		"size" => 30
    	));

    	$this->createAdd("ketai_number","HTMLInput",array(
    		"name" => "Detail[cellphoneNumber]",
    		"value" => $user->getCellphoneNumber()
    	));

    	$this->createAdd("office","HTMLInput",array(
    		"name" => "Detail[jobName]",
    		"value" => $user->getJobName(),
    		"style" => "width: 100%;"
    	));

    	$this->createAdd("office_post_number","HTMLInput",array(
    		"name" => "Detail[jobZipCode]",
    		"value" => $user->getJobZipCode()
    	));
    	
    	$this->createAdd("jobArea","HTMLSelect",array(
    		"name" => "Detail[jobArea]",
    		"options" => Area::getAreas(),
    		"selected" => $user->getJobArea()
    	));

    	$this->createAdd("jobAddress1","HTMLInput",array(
    		"name" => "Detail[jobAddress1]",
    		"value" => $user->getJobAddress1(),
    		"size" => 40
    	));

    	$this->createAdd("jobAddress2","HTMLInput",array(
    		"name" => "Detail[jobAddress2]",
    		"value" => $user->getJobAddress2(),
    		"style" => "width: 100%"
    	));

    	$this->createAdd("office_tel_number","HTMLInput",array(
    		"name" => "Detail[jobTelephoneNumber]",
    		"value" => $user->getJobTelephoneNumber(),
    		"size" => 30
    	));

    	$this->createAdd("office_fax_number","HTMLInput",array(
    		"name" => "Detail[jobFaxNumber]",
    		"value" => $user->getJobFaxNumber(),
    		"size" => 30
    	));

    	$this->createAdd("office_post_number","HTMLInput",array(
    		"name" => "Detail[jobZipCode]",
    		"value" => $user->getJobZipCode()
    	));
    	
    	$this->createAdd("memo","HTMLTextArea",array(
    		"name" => "Detail[memo]",
    		"text" => $user->getMemo(),
    		"style" => "width: 100%; padding: 2px; margin: 0;"
	   	));
    	
    	$this->createAdd("attribute1","HTMLInput",array(
    		"name" => "Detail[attribute1]",
    		"value" => $user->getAttribute1(),
    		"style" => "width: 70%;"
    	));

    	$this->createAdd("attribute2","HTMLInput",array(
    		"name" => "Detail[attribute2]",
    		"value" => $user->getAttribute2(),
    		"style" => "width: 70%;"
    	));
    	
    	$this->createAdd("attribute3","HTMLInput",array(
    		"name" => "Detail[attribute3]",
    		"value" => $user->getAttribute3(),
    		"style" => "width: 70%;"
    	));
    	
    	$this->createAdd("mail_send","HTMLCheckbox",array(
    		"name" => "Detail[notSend]",
    		"value" => 1,
    		"selected" => $user->getNotSend(),
    		"elementId" => "checkbox_send_email"
    	));
    	$this->createAdd("mail_send_hidden","HTMLInput",array(
    		"type" => "hidden",
			"name" => "Detail[isDisabled]",
    		"value" => 0,
    	));

    	$this->createAdd("mail_error_count","HTMLInput",array(
    		"name" => "disabled[mailErrorCount]",
    		"value" => $user->getMailErrorCount(),
    		"disabled" => "disabled"
    	));

    	$this->createAdd("register_date","HTMLInput",array(
    		"name" => "disabled[registerDate]",
    		"value" => date("Y-m-d", $user->getRegisterDate()),
    		"disabled" => "disabled"
    	));

    	$this->createAdd("update_date","HTMLInput",array(
    		"name" => "disabled[updateDate]",
    		"value" => date("Y-m-d", $user->getUpdateDate()),
    		"disabled" => "disabled"
    	));
    }
}
?>