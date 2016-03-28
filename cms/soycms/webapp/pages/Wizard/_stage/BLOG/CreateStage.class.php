<?php

class CreateStage extends StageBase{

    function CreateStage() {
    }
        
    function execute(){
		WebPage::WebPage();
		
		$this->createAdd("page_name","HTMLLabel",array(
			"text"=>(strlen($this->wizardObj->name))? $this->wizardObj->name : "[無題]"
		));
		
		$this->createAdd("page_url","HTMLLabel",array(
			"text"=>UserInfoUtil::getSiteUrl().$this->wizardObj->url
		));
		
		try{
			$template = $this->run("Template.TemplateDetailAction",array("id"=>$this->wizardObj->template_id))->getAttribute("entity");
		}catch(Exception $e){
			$template = new Template();
		}
		
		$this->createAdd("template_name","HTMLLabel",array(
			"text"=>$template->getName()
		));
    }
    
    function checkNext(){
    	
    	//validation
    	
//    	if(is_null(@$this->wizardObj->template_id)){
//    		return false;
//    	}
    	
    	if(is_null(@$this->wizardObj->pageType)){
    		return false;
    	}
    	
    	$request = SOY2ActionRequest::getInstance();
    	$dao = new SOY2DAO();
    	$dao->begin();
    	//ページを作る
    	$request->setParameter("uri",$this->wizardObj->url);
    	$request->setParameter("title",$this->wizardObj->name);
    	$request->setParameter("pageType",$this->wizardObj->pageType);
    	$request->setParameter("template",@$this->wizardObj->template_id);
    	
    	$result = $this->run("Page.CreateAction");
    	if(!$result->success()){
    		var_dump($result);
    		exit;
    		
    		$dao->rollback();
    		$this->addErrorMessage("WIZARD_CREATE_PAGE_FAILED");
    		return false;
    	}
    	
    	$pageId = $result->getAttribute("id");
    	
    	//ラベルを作る
    	$request->setParameter("caption",$this->wizardObj->name);
    	$result =$this->run("Label.LabelCreateAction"); 
    	if($result->success()){
    		$labelId = $result->getAttribute("id");
    		
    		//ブログにラベル付け
	    	$blog = $this->run("Blog.DetailAction",array("id"=>$pageId))->getAttribute("Page");
	    	$blog->setBlogLabelId($labelId);
	    	$blogDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
	    	try{
	    		$blogDAO->updatePageConfig($blog);
	    	}catch(Exception $e){
	    		$dao->rollback();
	    		$this->addErrorMessage("WIZARD_CREATE_PAGE_FAILED");
	    		return false;
	    	}
    	}
    	
    	$dao->commit();
    	
    	$this->wizardObj = new StdClass();
    	$this->wizardObj->pageId = $pageId;
    	
    	$this->addErrorMessage("WIZARD_CREATE_PAGE_SUCCESS");
    	return true;
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "BLOG.CreateFinishStage";
    }
    
    function getBackObject(){
    	return "BLOG.PageConfigStage";
    }
}
?>