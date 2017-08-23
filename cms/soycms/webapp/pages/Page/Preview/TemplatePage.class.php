<?php
SOY2::import('site_include.CMSPage');
SOY2::import('site_include.CMSBlogPage');
			
class TemplatePage extends CMSWebPageBase{
	
	var $pageId;
	var $mode;
	
	function doPost(){
    	if(soy2_check_token()){
			//Save the template
			if(isset($_POST["template_save_button"])){
				
				if($_POST["is_blog"]){
					
					switch($this->mode){
			    		case CMSBlogPage::MODE_ENTRY:
			    			$this->mode = "entry";
			    			break;
			    		case CMSBlogPage::MODE_POPUP:
			    			$this->mode = "popup";
			    			break;
			    		case CMSBlogPage::MODE_MONTH_ARCHIVE:
			    		case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
			    			$this->mode = "archive";
			    			break;
			    		case CMSBlogPage::MODE_TOP:
			    		default:
			    			$this->mode = "top";
			    			break;	
	    			}
					
					$result = $this->run("Blog.UpdateTemplateAction",array(
						"id"=> $this->pageId,
						"mode"=> $this->mode
					));
				
				}else{
					
					$result = $this->run("Page.UpdateTemplateAction",array(
						"id"=> $this->pageId
					));
				
				}
			}
    	}
	}
	
    function __construct($args) {
    	
    	$pageId = $args[0];
    	$mode = @$args[1];
    	
    	$this->pageId = $pageId;
    	$this->mode = $mode;    	
    	    	
    	parent::__construct();
    	
    	
    	$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
    	$page = $pageDAO->getById($pageId);
    	
    	$template = $page->getTemplate();
    	
    	if($page->isBlog()){
    		$blogPageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
    		$page = $blogPageDAO->getById($pageId);
    		
	    	SOY2::import('site_include.CMSPage');
			SOY2::import('site_include.CMSBlogPage');

    		switch($mode){
	    		case CMSBlogPage::MODE_ENTRY:
	    			$template = $page->getEntryTemplate();
	    			break;
	    		case CMSBlogPage::MODE_POPUP:
	    			$template = $page->getPopUpTemplate();
	    			break;
	    		case CMSBlogPage::MODE_MONTH_ARCHIVE:
	    		case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
	    			$template = $page->getArchiveTemplate();
	    			break;
	    		case CMSBlogPage::MODE_TOP:
	    		default:
	    			$template = $page->getTopTemplate();
	    			break;	
    		}
    	}
    	
    	//Blog page or not
    	$this->createAdd("is_blog","HTMLInput",array(
    		"value" => $page->isBlog(),
    		"name" => "is_blog"
    	));
    	
    	//Title
    	$this->createAdd("title","HTMLLabel",array(
    		"text" => $page->getTitle()
    	));
    	
    	//Form
    	$this->createAdd("template_form","HTMLForm");
    	
    	//TextArea
    	$this->createAdd("template","HTMLTextArea",array(
    		"name" => "template",
    		"value" => $template
    	));
    	
    	//Iframe
    	$this->createAdd("template_editor","HTMLModel",array(
    		"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
    		"onload" => "init_template_editor();"
    	));
    	
    	/* ------------ Following is settings of JS and CSS ------------------------------------ */
    	
    	//Insert CSS Saving path to JS
		HTMLHead::addScript("cssurl",array(
			"type"=>"text/JavaScript",
			"script"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
					  'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
					  'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));
    	
    	//Include Editor
		HTMLHead::addScript("TemplateEditor",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js") 
		));

		HTMLHead::addScript("cssMenu",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/cssMenu.js")
		));
		
		HTMLHead::addLink("editor.css",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/editor/editor.css")
		));
		
		HTMLHead::addScript("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));
		
		HTMLHead::addLink("form",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));
    }
}
?>