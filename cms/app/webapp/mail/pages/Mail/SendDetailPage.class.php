<?php

class SendDetailPage extends WebPage{
	
	var $id;

    function __construct($args) {
    	CMSApplication::setMode("layer");
    	
    	$this->id = (isset($args[0])) ? $args[0] : null;
    	
    	parent::__construct();
    	
    	try{
	    	$dao = SOY2DAOFactory::create("MailDAO");
		    $mail = $dao->getById($this->id);
    	}catch(Exception $e){
    		echo "情報の取得に失敗しました";
    		exit;
    	}
    	
    	$selector = $mail->getSelectorObject();
    	$total = $selector->countAddress();
    	
    	$this->createAdd("total","HTMLLabel",array(
    		"text" => number_format($total)
    	));
    	
    	$sendto = $selector->searchSendTo();
    	
    	$array = array();
    	foreach($sendto as $user){
    		$array[] = $user->getId(). ":" . $user->getMailAddress();
    	}
    	
    	$this->createAdd("sendto","HTMLLabel",array(
    		"text" => implode("\n",$array)
    	));
    }
}
?>