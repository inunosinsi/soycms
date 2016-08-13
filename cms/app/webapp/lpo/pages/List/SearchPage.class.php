<?php

SOY2::import("logic.PagerLogic");

class SearchPage extends WebPage{

    function __construct() {
    	
    	$limit = 15;
    	
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(is_null($page)) $page = 1;
		$offset = ($page - 1) * $limit;
		
		try{
			$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");
			$total = $dao->countByMode(SOYLpo_List::MODE_REFERER);
			$dao->setLimit($limit);
			$dao->setOffset($offset);
			$list = $dao->getByMode(SOYLpo_List::MODE_REFERER);
		}catch(Exception $e){
			$list = array();
		}
    	
    	
    	WebPage::__construct();
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => (count($list)==0)
    	));
    	
    	$this->createAdd("lpo_list","_common.LpoListComponent",array(
    		"list" => $list
    	));
    	
    	//ページャー
		$start = $offset;
		$end = $start + count($list);
		if($end > 0 && $start == 0)$start = 1;

		$pager = new PagerLogic();
		$pager->setPageUrl(SOY2PageController::createLink(APPLICATION_ID.".List.Search"));
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