<?Php

class CancelPage extends WebPage{
	
	function __construct($args){
		
		if(soy2_check_token() && isset($args[0])){
			$dao = SOY2DAOFactory::create("StepMail_NextSendDAO");
			try{
				$obj = $dao->getById($args[0]);
			}catch(Exception $e){
				CMSApplication::jump("User?failed");
			}
			
			$obj->setIsSended(StepMail_NextSend::IS_SENDED);
			
			try{
				$dao->update($obj);
			}catch(Exception $e){
				CMSApplication::jump("User?failed");
			}
			
			CMSApplication::jump("User?canceled");
		}
	}
}
?>