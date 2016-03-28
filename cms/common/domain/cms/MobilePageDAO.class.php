<?php
/**
 * @entity cms.MobilePage
 */
class MobilePageDAO{
	
	function get(){
		$dao = $this->getPageDAO();
		return $dao->getByPageType(Page::PAGE_TYPE_MOBILE);
	}
	
	/**
	 * IDを指定して取得
	 */
	function getById($id){
		
		$dao = $this->getPageDAO();
		$obj = $dao->getById($id);
		
		if($obj->getPageType() != Page::PAGE_TYPE_MOBILE){
			throw new Exception("This Page is not Mobile Page.");
		}
		
		//MobilePageにCast
		$mobilePage = SOY2::cast("MobilePage",$obj);
		$config = $mobilePage->getPageConfigObject();
    	
    	if($config){
    		$config = unserialize($mobilePage->getPageConfig());
    		SOY2::cast($mobilePage,$config);
    	}
    	
    	//Rootが無い場合
    	if(count($mobilePage->getVirtual_tree())==0){
    		$rootTree = $this->getRootVirtualTreePage();
			$mobilePage->insertVirtual_tree($rootTree);	
    	}
    	
    	return $mobilePage;		
	}
	
	function updatePageConfig(MobilePage $page){
		
		$dao = $this->getPageDAO();
		$_page = $dao->getById($page->getId());
		
		//テンプレートは更新しない
		$page->setTemplate($_page->getTemplate());
		
		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		$dao->update($page);
		$dao->updatePageConfig($page);		
	}
	
	function update(MobilePage $page){
		$dao = $this->getPageDAO();
		$dao->update($page);
	}
	
	function getPageDAO(){
		return SOY2DAOFactory::create("cms.PageDAO");
	}
	
	function insert(Page $page){
		
		$dao = $this->getPageDAO();
		$page = SOY2::cast("MobilePage",$page);
		
		//初期データ
		$rootTree = $this->getRootVirtualTreePage();
		$page->insertVirtual_tree($rootTree);
				
		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		
		$id = $dao->insert($page);
		
		return $id;
	}
	
	function getRootVirtualTreePage(){
		$rootTree = new VirtualTreePage();
		$rootTree->setType(VirtualTreePage::TYPE_ROOT);
		$rootTree->setId(0);
		$rootTree->setTitle("ROOT");
		return $rootTree;
	}

}
?>
