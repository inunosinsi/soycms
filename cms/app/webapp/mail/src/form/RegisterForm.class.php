<?php
class RegisterForm extends SOYBodyComponentBase{

	private $mailaddress;

    function getStartTag(){
    	
    	$action = htmlspecialchars($_SERVER["REQUEST_URI"],ENT_QUOTES);
    	
    	$html = '<form action="'.$action.'" method="post">';
    	$html .= '<input type="hidden" name="register" value="1" />';
    	$html .= '<input type="hidden" name="sid" value="<?php echo session_id() ?>" />';
    	
    	return parent::getStartTag() . $html;
    }
    
    function execute(){
    	
    	$this->createAdd("error_message","HTMLModel",array(
			"visible" => (isset($_GET["failed"])),
    		"soy2prefix" => "cms"
    	));
    	
    	$this->createAdd("mailaddress","HTMLInput",array(
    		"id" => "soymail_input_register_mailaddress",
    		"name" => "mailaddress",
    		"value" => $this->getMailaddress(),
    		"soy2prefix" => "cms" 	
    	));
    	
    	parent::execute();
    }
    
    function getEndTag(){
    	$html = "</form>";
    	return $html . parent::getEndTag();
    }
    
    function getMailaddress(){
    	return $this->mailaddress;
    }
    function setMailaddress($mailaddress){
    	$this->mailaddress = $mailaddress;
    }
}
?>