<?php

class CopyPage extends CMSWebPageBase{

    function CopyPage($args) {

    	if(soy2_check_token()){
	    	$id = $args[0];

	    	$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
	    	$blockDAO = SOY2DAOFactory::create("cms.BlockDAO");

	    	try{
	    		$page = $pageDAO->getById($id);

	    		if($page->getPageType() == Page::PAGE_TYPE_ERROR){
	    			throw new Exception("The 404 Not Found Page cannot be copied.");
	    		}

	    		$page->setTitle($this->getMessage("SOYCMS_COPY_MESSAGE") . $page->getTitle());
	    		$page->setUri($page->getUri()."_" . time());

	    		$blocks = $blockDAO->getByPageId($id);

	    		$page->setId(null);
	    		$id = $pageDAO->insert($page);

	    		$page->setId($id);
	    		$page->setIsPublished(false);

	    		foreach($blocks as $block){
	    			$block->setPageId($id);

	    			$blockDAO->insert($block);
	    		}

				$this->jump("Page.Detail.".$id."?msg=create");

	    	}catch(Exception $e){
	    		//
	    	}
    	}

    	$this->jump("Page");
    	exit;
    }
}
?>