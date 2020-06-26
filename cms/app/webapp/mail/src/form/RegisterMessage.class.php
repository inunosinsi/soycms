<?php
class RegisterMessage extends SOYBodyComponentBase{

	private $mailaddress;

    function execute(){
    	
    	$this->createAdd("mailaddress","HTMLLabel",array(
    		"text" => $this->mailaddress,
    		"soy2prefix" => "cms"
    	));
    	
    	parent::execute();
    }

    function getMailaddress() {
    	return $this->mailaddress;
    }
    function setMailaddress($mailaddress) {
    	$this->mailaddress = $mailaddress;
    }
}
?>