<?php

class UserPagePagerLogic extends SOY2LogicBase{

    private $pageURL;
	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $query;
	private $pagerCount = 10; //ページャーを何個表示するか(-1だと全て)
	private $limit = 15;

	function setPageURL($value){
		$this->pageURL = $value;
	}
	function setPage($value){
		$this->page = $value;
	}
	function setStart($value){
		$this->start = $value;
	}
	function setEnd($value){
		$this->end = $value;
	}
	function setTotal($value){
		$this->total = $value;
	}
	function setLimit($value){
		$this->limit = $value;
	}

	function getCurrentPageURL(){
		return $this->pageURL.$this->page;
	}
	function getPageURL(){
		return $this->pageURL;
	}
	function getPage(){
		return $this->page;
	}
	function getStart(){
		return $this->start;
	}
	function getEnd(){
		return $this->end;
	}
	function getTotal(){
		return $this->total;
	}
	function getLimit(){
		return $this->limit;
	}
	function getOffset(){
		return ($this->page - 1) * $this->limit;
	}

	function getNextParam(){
		$link = ($this->total > $this->end) ? $this->pageURL . ($this->page + 1) : $this->pageURL . $this->page;
		if(strlen($this->getQuery()))$link .= "&" . $this->getQuery();

		return array(
    		"link" => $link,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : "",
    		"visible" => !($this->total <= $this->end)
    	);
	}
	function getPrevParam(){
		$link = ($this->page > 1) ? $this->pageURL . ($this->page - 1) : $this->pageURL . ($this->page);
		if(strlen($this->getQuery()))$link .= "" . $this->getQuery();
		return array(
    		"link" => $link,
    		"class" => ($this->page <= 1) ? "pager_disable" : "",
    		"visible" => !($this->page <= 1)
    	);
	}
	function getPagerParam(){
    	$pagers = range(
    		max(1, $this->page - 3),
    		min(ceil($this->total / $this->limit), $this->page + 3)
    	);

		return array(
    		"url" => $this->pageURL,
    		"current" => $this->page,
    		"list" => $pagers
    	);
	}
	function getSelectArray(){
    	$pagers = range(
    		1,
    		(int)($this->total / $this->limit) + 1
    	);

		$array = array();
		foreach($pagers as $page){
			$array[ $page ] = $page;
		}

		return $array;
	}

	function buildPager($htmlObj,$adminPage=true){

		$pager = $this;

		//件数情報表示
		$htmlObj->createAdd("count_start","HTMLLabel", array(
			"text" => $pager->getStart()
		));
		$htmlObj->createAdd("count_end","HTMLLabel", array(
			"text" => $pager->getEnd()
		));
		$htmlObj->createAdd("count_max","HTMLLabel", array(
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$htmlObj->createAdd("next_pager","HTMLLink",$pager->getNextParam());
		$htmlObj->createAdd("prev_pager","HTMLLink",$pager->getPrevParam());
		$htmlObj->createAdd("pager_list","UserPagePager",$pager->getPagerParam());

		//ページへジャンプ
		$htmlObj->createAdd("pager_jump","HTMLForm", array(
			"method" => "get",
			"action" => $pager->getPageURL()
		));
		$htmlObj->createAdd("pager_select","HTMLSelect", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
	}

	function getQuery() {
		return $this->query;
	}
	function setQuery($query) {
		$this->query = $query;
	}
}
/**
 * GET使用
 */
class UserPagePager extends HTMLList{

	private $url;
	private $current;
	private $query;

	protected function populateItem($bean){
		$url = $this->url . $bean;
		if(strlen($this->query))$url .= "&" . $this->query;

		$this->createAdd("target_link","HTMLLink", array(
			"text" => $bean,
			"link" => $url,
			"class" => ($this->current == $bean) ? "pager_current" : ""
		));
	}

	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getCurrent() {
		return $this->current;
	}
	function setCurrent($cuttent) {
		$this->current = $cuttent;
	}
	function setQuery($query){
		$this->query = $query;
	}
}

?>