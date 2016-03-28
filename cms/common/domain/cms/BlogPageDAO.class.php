<?php
/**
 * @entity cms.BlogPage
 */
class BlogPageDAO{
	
	function get(){
		$dao = $this->getPageDAO();
		$pages = $dao->getByPageType(Page::PAGE_TYPE_BLOG);
		
		foreach($pages as $key => $page){
			$pages[$key] = $this->cast($page);
		}
		
		return $pages;
	}
	
	/**
	 * IDを指定して取得
	 */
	function getById($id){
		
		$dao = $this->getPageDAO();
		$obj = $dao->getById($id);
		
		if($obj->getPageType() != Page::PAGE_TYPE_BLOG){
			throw new Exception("This Page is not Blog Page.");
		}
		
		return $this->cast($obj);
	}
	
	function cast($page){
		$blogPage = SOY2::cast("BlogPage",$page);
		
		$config = $blogPage->getPageConfigObject();
    	
    	if($config){
    		$config = unserialize($blogPage->getPageConfig());
    		SOY2::cast($blogPage,$config);
    	}
    	
    	return $blogPage;		
	}	
	
	/**
	 * BlogPageを初期化する
	 */
	function insert(Page $page){
		
		$dao = $this->getPageDAO();
		$page = SOY2::cast("BlogPage",$page);
		
		//初期データ
		$page->setEntryPageUri("article");
		$page->setRssPageUri("feed");
		$page->setCategoryPageUri("category");
		$page->setMonthPageUri("month");
		$page->setTopDisplayCount(10);
		$page->setCategoryDisplayCount(10);
		$page->setMonthDisplayCount(10);
		$page->setRssDisplayCount(10);
		$page->setGenerateCategoryFlag(true);
		$page->setGenerateEntryFlag(true);
		$page->setGenerateTopFlag(true);
		$page->setGenerateRssFlag(true);
		$page->setGenerateMonthFlag(true);
		$page->setTopTitleFormat("%BLOG%");
		$page->setEntryTitleFormat("%ENTRY% - %BLOG%");
		$page->setMonthTitleFormat("%YEAR%-%MONTH% - %BLOG%");
		$page->setCategoryTitleFormat("%CATEGORY% - %BLOG%");
		
		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		
		$id = $dao->insert($page);
		
		return $id;
	}
	
	/**
	 * ページの設定を更新する
	 */
	function updatePageConfig(BlogPage $page){
		
		$dao = $this->getPageDAO();
		$_page = $dao->getById($page->getId());
		
		//テンプレートは更新しない
		$page->setTemplate($_page->getTemplate());
		
		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		$dao->update($page);
		$dao->updatePageConfig($page);		
	}
	
	function update(BlogPage $page){
		$dao = $this->getPageDAO();
		$dao->update($page);
	}
	
	function getPageDAO(){
		return SOY2DAOFactory::create("cms.PageDAO");
	}
	
}
?>
