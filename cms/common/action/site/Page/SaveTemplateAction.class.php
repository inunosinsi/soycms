<?php

class SaveTemplateAction extends SOY2Action {
	
	private $id;
	private $type;
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	try{
    		$template = $request->getParameter("template");
    		
    		$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
    		$pageId = $this->id[0];	//最初の配列にはpageIDが入っている
    		$page = $pageDAO->getById($pageId);
    		
    		//ブログの場合はタイプも一緒に送る
    		$this->type = (isset($this->id[1])) ? $this->id[1] : null;
    		
    		if($this->type){
    			$tempArray = $page->getTemplate();
    			$tempArray = unserialize($tempArray);
    			
    			$tempArray[$this->type] = $template;
    			
    			$page->setTemplate(serialize($tempArray));
    			
    		}else{
    			$page->setTemplate($template);
    		}
    		$pageDAO->update($page);
    		
    	}catch(Exception $e){
    		SOY2Action::FAILED;
    	}
    	
    	return SOY2Action::SUCCESS;
    }

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }

    function getType() {
    	return $this->type;
    }
    function setType($type) {
    	$this->type = $type;
    }
}
