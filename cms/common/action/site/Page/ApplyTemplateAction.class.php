<?php

class ApplyTemplateAction extends SOY2Action{

	private $pageId;
	private $targetPage;
	private $mode;
    function execute($request,$form,$response) {
    	
    	if(!$form->template){
    		return SOY2Action::SUCCESS;
    	}
    	
    	try{
	    	$pagelogic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
	    	$pageObj = $pagelogic->getById($this->pageId);

			$createlogic = SOY2Logic::createInstance("logic.site.Page.CreatePageLogic");

	    	switch($pageObj->getPageType()){
	    		case Page::PAGE_TYPE_BLOG:
	    			$templateDAO = SOY2DAOFactory::create("cms.TemplateDAO");

	    			$templateObj = $templateDAO->getById($form->template);
	    			$contents = $templateObj->getTemplateContent();

					//@@TITLE@@, @@ENCODING@@を置換
					$contents = $createlogic->replaceTitle($contents, $pageObj->getTitle());
					$contents = $createlogic->replaceEncoding($contents);
	    			
	    			if(is_null($this->mode)){
	    				$currentTemplate = $contents;
	    			}else{
		    			$currentTemplate = unserialize($pageObj->getTemplate());
	    				$currentTemplate[$this->mode] = $contents[$this->mode];
	    			}

	    			$pageObj->setTemplate(serialize($currentTemplate));
	    			$pagelogic->update($pageObj);
	    			break;
	    		default:
	    			$tmplogic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
					$template = $form->template;
					
					//list($id,$name)= explode("/",$template);
					$id = $template;	// id/nameの形式を廃止
					$dao = SOY2DAOFactory::create("cms.TemplateDAO");
			    	$template = $dao->getById($id);
					$tmps = $template->getTemplate();
					$keys = array_keys($tmps);
					
			    	$contents = $template->getTemplateContent($keys[0]);

					//@@TITLE@@, @@ENCODING@@を置換
					$contents = $createlogic->replaceTitle($contents, $pageObj->getTitle());
					$contents = $createlogic->replaceEncoding($contents);

			    	$pageObj->setTemplate($contents);
	    			$pagelogic->update($pageObj);
	    			break;
	    	}
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}

    	return SOY2Action::SUCCESS;

    }

    function getPageId() {
    	return $this->pageId;
    }
    function setPageId($pageId) {
    	$this->pageId = $pageId;
    }

    function getMode() {
    	return $this->mode;
    }
    function setMode($mode) {
    	$this->mode = $mode;
    }
}

class ApplyTemplateActionForm extends SOY2ActionForm{
	var $template;


	function getTemplate() {
		return $this->template;
	}

	/**
	 * @validator string {"require":true}
	 */
	function setTemplate($templateId) {
		$this->template = $templateId;
	}
}
?>