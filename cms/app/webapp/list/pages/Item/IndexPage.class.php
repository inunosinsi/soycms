<?php

SOY2::import("logic.PagerLogic");

class IndexPage extends WebPage{
	
	private $categoryDao;

    function __construct($args) {
    	
    	$page = null;
    	
    	$limit = 15;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(is_null($page)) $page = 1;
		$offset = ($page - 1) * $limit;
		
		$dao = SOY2DAOFactory::create("SOYList_ItemDAO");
		$total = $dao->count();
		$dao->setLimit($limit);
		$dao->setOffset($offset);
		
		try{			
			$items = $dao->get();
		}catch(Exception $e){
			$items = array();
		}
    	
    	
    	WebPage::WebPage();
    	
    	$categories = $this->getCategories();
    	
    	$this->createAdd("is_category","HTMLModel",array(
    		"visible" => (count($categories) > 0)
    	));
    	
    	$this->createAdd("item_list","_common.ItemListComponent",array(
    		"list" => $items,
    		"categories" => $categories
    	));
    	
    	//ページャー
		$start = $offset;
		$end = $start + count($items);
		if($end > 0 && $start == 0)$start = 1;

		$pager = new PagerLogic();
		$pager->setPageUrl(SOY2PageController::createLink(APPLICATION_ID.".Item"));
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		
		try{
			$this->buildPager($pager);
		}catch(Exception $e){
			//var_dump($e);
		}
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($items) == 0
    	));
    }
    
    
    function getCategories(){
    	if(!$this->categoryDao)$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	try{
    		$categories = $this->categoryDao->get();
    	}catch(Exception $e){
    		$categories = array();
    	}
    	
    	$array = array();
    	
    	foreach($categories as $category){
    		$array[$category->getId()] = $category->getName();
    	}
    	
    	return $array;
    }
    
    function buildPager(PagerLogic $pager){

		//件数情報表示
		$this->createAdd("count_start","HTMLLabel",array(
			"text" => $pager->getStart()
		));
		$this->createAdd("count_end","HTMLLabel",array(
			"text" => $pager->getEnd()
		));
		$this->createAdd("count_max","HTMLLabel",array(
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->createAdd("next_pager","HTMLLink",$pager->getNextParam());
		$this->createAdd("prev_pager","HTMLLink",$pager->getPrevParam());
		$this->createAdd("pager_list","SimplePager",$pager->getPagerParam());
		
		//ページへジャンプ
		$this->createAdd("pager_jump","HTMLForm",array(
			"method" => "get",
			"action" => $pager->getPageURL()."/"
		));
		$this->createAdd("pager_select","HTMLSelect",array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
	}
}
?>