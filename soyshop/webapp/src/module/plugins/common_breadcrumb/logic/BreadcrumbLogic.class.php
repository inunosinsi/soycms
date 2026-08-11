<?php
SOY2::imports("module.plugins.common_breadcrumb.domain.*");
class BreadcrumbLogic extends SOY2LogicBase{
	
	private $pageDao;
	private $breadcrumbDao;
	
	function getPages(){
		if(!$this->pageDao){
			$this->pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		}
		
		$type = "list";
		try{
			$pages = $this->pageDao->getByType($type);
		}catch(Exception $e){
			$pages = array();
		}
		
		return $pages;
	}
	
	function getListPageId($itemId){
		$breadcrumbDao = $this->getBreadcrumbDao();
		try{
			$page = $breadcrumbDao->getByItemId($itemId);
			$pageId = $page->getPageId();
		}catch(Exception $e){
			$pages = $this->getPages();
			if(count($pages) > 0){
				$page = array_shift($pages);
				$pageId = $page->getId();
			}else{
				$pageId = null;
			}
		}	
		return $pageId;
	}
	
	function getPageUri($pageId){
		if(!$this->pageDao){
			$this->pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		}
		try{
			$page = $this->pageDao->getById($pageId);
		}catch(Exception $e){
			$page = new SOYShop_Page();
		}
		return $page->getUri();
	}

	function insert($itemId, $pageId){
		$dao = $this->getBreadcrumbDao();
		$obj = new SOYShop_Breadcrumb();
		$obj->setItemId($itemId);
		$obj->setPageId($pageId);
		try{
			$dao->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}
		try{
			$dao->insert($obj);
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}

	function deleteItem($id){
		$dao = $this->getBreadcrumbDao();
		try{
			$dao->deleteByItemId($id);
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	function deletePage($id){
		$dao = $this->getBreadcrumbDao();
		try{
			$dao->deleteByPageId($id);
		}catch(Exception $e){
			return false;
		}		
		return true;
	}
	
	function getBreadcrumbDao(){
		if(!$this->breadcrumbDao){
			$this->breadcrumbDao = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO");
		}
		return $this->breadcrumbDao;
	}
}
?>