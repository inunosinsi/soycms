<?php
/**
 * @entity cms.BlogPage
 */
class BlogPageDAO{

	function get(){
		$pages = self::_dao()->getByPageType(Page::PAGE_TYPE_BLOG);

		if(count($pages)){
			foreach($pages as $key => $page){
				$pages[$key] = $this->cast($page);
			}
		}

		return $pages;
	}

	/**
	 * IDを指定して取得
	 */
	function getById(int $id){
		$obj = self::_dao()->getById($id);
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

		$dao = self::_dao();
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

		$dao = self::_dao();
		$_page = $dao->getById($page->getId());

		//テンプレートは更新しない
		$page->setTemplate($_page->getTemplate());

		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		$dao->update($page);
		$dao->updatePageConfig($page);
	}

	function update(BlogPage $page){
		self::_dao()->update($page);
	}

	/**
	 * @final
	 * @return array(label_id => array(uri...)...)
	 */
	function getBlogPageUriListCorrespondingToBlogLabelId(){
		$dao = self::_dao();
		try{
			$res = $dao->executeQuery("SELECT uri, page_config FROM Page WHERE page_type = " . Page::PAGE_TYPE_BLOG);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$cnf = soy2_unserialize($v["page_config"]);
			if(!property_exists($cnf, "blogLabelId")) continue;

			if(!isset($list[$cnf->blogLabelId])) $list[$cnf->blogLabelId] = array();
			$list[$cnf->blogLabelId][] = $v["uri"];
		}
		return $list;
	}

	private function _dao(){
		return SOY2DAOFactory::create("cms.PageDAO");
	}
}
