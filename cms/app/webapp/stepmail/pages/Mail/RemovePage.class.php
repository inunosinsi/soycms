<?php

class RemovePage extends WebPage{
	
	function RemovePage($args){
		
		if(soy2_check_token() && isset($args[0])){
			
			$dao = SOY2DAOFactory::create("StepMail_MailDAO");
			try{
				$mail = $dao->getById($args[0]);
			}catch(Exception $e){
				CMSApplication::jump("Mail?failed");
			}
			
			$mail->setIsDisabled(StepMail_Mail::IS_DISABLED);
			try{
				$dao->update($mail);
			}catch(Exception $e){
				CMSApplication::jump("Mail?failed");
			}
			
			//紐づいているStepを削除
			$dao = SOY2DAOFactory::create("StepMail_StepDAO");
			try{
				$array = $dao->getByMailId($args[0]);
			}catch(Exception $e){
				$array = array();
			}
			
			foreach($array as $obj){
				$obj->setIsDisabled(StepMail_Step::IS_DISABLED);
				try{
					$dao->update($obj);
				}catch(Exception $e){
					//
				}
			}
			
			CMSApplication::jump("Mail?successed");
		}
		
		CMSApplication::jump("Mail?failed");
	}
}
?>