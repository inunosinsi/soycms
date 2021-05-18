<?php

class UpdateTemplateAction extends SOY2Action{

	private $id;
	private $mode;

	function setId($id) {
    	$this->id = $id;
    }

    function setMode($mode) {
    	$this->mode = $mode;
    }

    function execute($request,$form,$response) {
    	$template = $form->template;

		$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	$page = $dao->getById($this->id);
		$old = $page;

    	$templateArray = $page->_getTemplate();
    	switch($this->mode){
    		case "entry":
    			$templateArray[BlogPage::TEMPLATE_ENTRY] = $template;
    			break;
    		case "popup":
    			$templateArray[BlogPage::TEMPLATE_POPUP] = $template;
    			break;
    		case "top":
    			$templateArray[BlogPage::TEMPLATE_TOP] = $template;
    			break;
    		case "archive":
    		default:
    			$templateArray[BlogPage::TEMPLATE_ARCHIVE] = $template;
    	}

		$page->setTemplate(serialize($templateArray));
		$dao->update($page);

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onBlogPageUpdate', array("new_page" => $page, "old_page" => $old));

		return SOY2Action::SUCCESS;
    }
}

class UpdateTemplateActionForm extends SOY2ActionForm{
	var $template;

	function setTemplate($template){
		$this->template = $template;
	}
}
