<?php

class ExportTemplatePage extends CMSWebPageBase{

    var $pageId;

    function __construct($arg) {
    	$this->pageId = isset($arg[0])? $arg[0] : null;
    	$this->mode = isset($arg[1])? $arg[1] : null;
    	
    	if(is_null($this->pageId)){
    		header('Content-Disposition: attachment;filename=blank.html;');
    		echo "";
    	}else{
    		parent::__construct();
    		$page = $this->getPageObject($this->pageId);
    		
    		if($page->getPageType() != Page::PAGE_TYPE_BLOG){
    			header('Content-Disposition: attachment;filename=blank.html;');
    			echo "";
    		}else{
    		
	    		//テンプレート別の動作
		    	switch($this->mode){
		    		case "entry":
		    			$template = $page->getEntryTemplate();
		    			break;
		    		case "popup":
		    			$template = $page->getPopUpTemplate();
		    			break;
		    		case "top":
		    			$template = $page->getTopTemplate();
		    			break;
		    		case "archive":
		    		default:
		    			$template = $page->getArchiveTemplate();
		    	}
		    	
		    	$filename = "template_".str_replace("/","_",$page->getUri())."_".$this->mode.".html";
	    		
	    		header('Content-Disposition: attachment;filename='.$filename.';');
	    		echo $template;
    		}
    		
    	}
    	
    	exit;
    }
    
    function getTemplate(){
    	return "";
    }
    
    function getPageObject($id){
    	return SOY2ActionFactory::createInstance("Blog.DetailAction",array(
    		"id" => $id
    	))->run()->getAttribute("Page");
    }
}
?>