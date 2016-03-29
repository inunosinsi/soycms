<?php

class MoveDraftPage extends WebPage{

    function MoveDraftPage($args) {
    	$id = @$args[0];
    	
    	$mailDAO = SOY2DAOFactory::create("MailDAO");
    	try{
    		$mail = $mailDAO->getById($id);
    		if($mail->getStatus() == Mail::STATUS_HISTORY){
    			throw new Exception("送信完了してます");
    		}
    		$mail->setStatus(Mail::STATUS_DRAFT);
    		$mailDAO->update($mail);
    	}catch(Exception $e){
    		CMSApplication::jump("Mail.HistoryBox");
    	}
    		
    	CMSApplication::jump("Mail.DraftBox");
    }
}
?>