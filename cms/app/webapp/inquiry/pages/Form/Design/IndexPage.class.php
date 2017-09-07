<?php

class IndexPage extends WebPage{
	var $id;
	var $dao;
	var $columnDao;
	var $form;
	var $errorMessage;
	
	function doPost(){
		
	}
	
	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$this->form = new SOYInquiry_Form();
    	
    	$this->columnDao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    	
    	parent::prepare();
	}	
	
    function __construct($args) {
    	if(count($args)<1)CMSApplication::jump("Form");
    	$this->id = $args[0];
    	
    	parent::__construct();
    	
    	try{
    		$this->form = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		CMSApplication::jump("Form");
    	}
    	
    	$this->createAdd("form_name","HTMLLabel",array(
    		"text" => $this->form->getName()
    	));
    	
    	$this->createAdd("config_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config.".$this->id)
    	));
    	
    	$this->createAdd("preview_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Preview.".$this->id),
    		"onclick" => "if(window.previewframe)window.previewframe.close();window.previewframe = new soycms.UI.TargetWindow(this);return false;"
    	));
    	
    	$this->createAdd("template_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Template.".$this->id),
    	));
    	
    	$this->createAdd("column_fr","HTMLModel",array(
    		"src" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.Column.".$this->id)
    	));
    	
    	$this->createAdd("add_column_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.AddColumn.".$this->id)
    	));
    	
    	$this->createAdd("change_order_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.ChangeOrder.".$this->id)
    	));
    	
    	  	
    }
}

?>