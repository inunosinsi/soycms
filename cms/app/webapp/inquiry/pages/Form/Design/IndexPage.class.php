<?php

SOY2HTMLFactory::importWebPage("_common.FormPageBase");
class IndexPage extends FormPageBase{
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

    	$this->addLabel("form_name", array(
    		"text" => $this->form->getName()
    	));

    	$this->addLink("config_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config.".$this->id)
    	));

    	// $this->createAdd("preview_link","HTMLLink",array(
    	// 	"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Preview.".$this->id),
    	// 	"onclick" => "if(window.previewframe)window.previewframe.close();window.previewframe = new soycms.UI.TargetWindow(this);return false;"
    	// ));

		$this->addLabel("preview_modal", array(
			"html" => $this->buildModal($this->id, self::MODE_PREVIEW)
		));

    	$this->addLink("template_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Template.".$this->id),
    	));

    	$this->addModel("column_fr", array(
    		"src" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.Column.".$this->id)
    	));

    	$this->addLink("add_column_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.AddColumn.".$this->id)
    	));
		$this->addLabel("add_modal", array(
			"html" => $this->buildModal($this->id, self::MODE_ADD)
		));

    	$this->addLink("change_order_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.ChangeOrder.".$this->id)
    	));
		$this->addLabel("change_modal", array(
			"html" => $this->buildModal($this->id, self::MODE_CHANGE)
		));
    }
}
