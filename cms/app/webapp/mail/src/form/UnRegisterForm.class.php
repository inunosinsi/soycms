<?php
class UnRegisterForm extends SOYBodyComponentBase{

    function getStartTag(){
    	$action = htmlspecialchars($_SERVER["REQUEST_URI"],ENT_QUOTES);
    	
    	$html = '<form action="'.$action.'" method="post">';
    	$html .= '<input type="hidden" name="unregister" value="1" />';
    	$html .= '<input type="hidden" name="sid" value="<?php echo session_id() ?>" />';
    	
    	return parent::getStartTag() . $html;
    }
    
    function execute(){
    	
    	$this->createAdd("mailaddress","HTMLInput",array(
    		"id" => "soymail_input_unregister_mailaddress",
			"name" => "mailaddress",
			"soy2prefix" => "cms"
    	));
    	
    	parent::execute();
    }
    
    function getEndTag(){
    	$html = "</form>";
    	return $html . parent::getEndTag();
    }
    
}
?>