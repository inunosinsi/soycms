<?php

class TemplatePage extends WebPage{

    function __construct($args) {
    	if(count($args)<1)CMSApplication::jump("Form");
    	$this->id = $args[0];

    	WebPage::__construct();

    	try{
			$dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    		$this->form = $dao->getById($this->id);
    	}catch(Exception $e){
    		CMSApplication::jump("Form");
    	}

    	$this->createAdd("form_name","HTMLLabel",array(
    		"text" => $this->form->getName()
    	));

		$template = array(
			'<!-- app:id="soyinquiry" app:formid="'.htmlspecialchars($this->form->getFormId(), ENT_QUOTES, "UTF-8").'" -->',
			'ここにフォームが表示されます。',
			'<!-- /app:id="soyinquiry" -->',
		);
    	$this->createAdd("template","HTMLLabel",array(
    		"text" => implode("\n", $template),
    		"onclick" => "this.select()"
    	));

    	$cmspage = '<!-- cms:id="apps" cms:app="inquiry" /-->';
    	$this->createAdd("template2","HTMLLabel",array(
    		"text" => $cmspage."\n".implode("\n", $template),
    		"onclick" => "this.select()"
    	));


    	$this->createAdd("design_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.".$this->id)
    	));

    	$this->createAdd("config_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config.".$this->id)
    	));

    	$this->createAdd("preview_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Preview.".$this->id),
    		"onclick" => "if(window.previewframe)window.previewframe.close();window.previewframe = new soycms.UI.TargetWindow(this);return false;"
    	));


    }
}
?>