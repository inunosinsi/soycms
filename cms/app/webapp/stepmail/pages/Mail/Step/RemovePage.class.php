<?php

class RemovePage extends WebPage{
	
	function RemovePage($args){
		
		if(soy2_check_token() && isset($args[0])){
			
			$dao = SOY2DAOFactory::create("StepMail_StepDAO");
			try{
				$step = $dao->getById($args[0]);
			}catch(Exception $e){
				CMSApplication::jump("Mail.Detail." . $_GET["mail_id"] . "?failed");
			}
			
			$step->setIsDisabled(StepMail_Step::IS_DISABLED);
			try{
				$dao->update($step);
				CMSApplication::jump("Mail.Detail." . $_GET["mail_id"] . "?successed");
			}catch(Exception $e){
				
			}
		
		CMSApplication::jump("Mail.Detail." . $_GET["mail_id"] . "?failed");
		}
	}
}
?>