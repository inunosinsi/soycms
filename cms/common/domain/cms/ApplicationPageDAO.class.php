<?php
/**
 * @entity cms.ApplicationPage
 */
class ApplicationPageDAO {

    function get(){
		$dao = $this->getPageDAO();
		return $dao->getByPageType(Page::PAGE_TYPE_APPLICATION);
	}
	
	/**
	 * IDを指定して取得
	 */
	function getById($id){
		
		$dao = $this->getPageDAO();
		$obj = $dao->getById($id);
		
		if($obj->getPageType() != Page::PAGE_TYPE_APPLICATION){
			throw new Exception("This Page is not Application Page.");
		}
		

		$page = SOY2::cast("ApplicationPage",$obj);
		
		$config = $page->getPageConfigObject();
    	
    	if($config){
    		$config = unserialize($page->getPageConfig());
    		SOY2::cast($page,$config);
    	}
    	
    	return $page;		
	}
	
	function updatePageConfig(ApplicationPage $page){
		
		$dao = $this->getPageDAO();
		$_page = $dao->getById($page->getId());
		
		//テンプレートは更新しない
		$page->setTemplate($_page->getTemplate());
		
		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		$dao->update($page);
		$dao->updatePageConfig($page);		
	}
	
	function getPageDAO(){
		return SOY2DAOFactory::create("cms.PageDAO");
	}
}
?>