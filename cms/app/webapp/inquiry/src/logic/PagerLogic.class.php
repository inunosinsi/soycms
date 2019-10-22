<?php

class PagerLogic {
	private $pageURL;
	private $query;
	private $queryString;

	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $limit = 15;

	function __construct(){}

	function setPageURL($value){
		$this->pageURL = SOY2PageController::createLink($value);
	}
	function getQuery() {
		return $this->query;
	}
	function setQuery($query) {
		$this->query = $query;
		$this->queryString = (count($query) > 0) ? "?".http_build_query($query) : "" ;
	}
	function getQueryString() {
		return $this->queryString;
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
		return $this->pageURL."/".$this->page;
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
		return ($this->page - 1) * $this->limit;;
	}

	function getNextParam(){
		return array(
    		"link" => ( ($this->total > $this->end) ? $this->pageURL . "/" . ($this->page + 1) : $this->pageURL . "/" . $this->page ) . $this->queryString,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : ""
    	);
	}
	function getPrevParam(){
		return array(
    		"link" => ( ($this->page > 1) ? $this->pageURL . "/" . ($this->page - 1) : $this->pageURL . "/" . ($this->page) ) . $this->queryString,
    		"class" => ($this->page <= 1) ? "pager_disable" : ""
    	);
	}
	function getPagerParam(){
    	$pagers = range(
    		max(1, $this->page - 4),
    		max(1, min(ceil($this->total / $this->limit), max(1, $this->page - 4) +9))
    	);

		return array(
    		"url" => $this->pageURL,
    		"queryString" => $this->queryString,
    		"current" => $this->page,
    		"list" => $pagers
    	);
	}
	function getSelectArray(){
    	$pagers = range(
    		1,
    		ceil($this->total / $this->limit)
    	);

		$array = array();
		foreach($pagers as $page){
			$array[ $this->pageURL."/".$page . $this->queryString ] = $page;
//			$array[ $page ] = $page;
		}

		return $array;
	}

}

class SimplePager extends HTMLList{

	private $url;
	private $queryString;
	private $current;

	protected function populateItem($bean){

		$this->createAdd("target_link","HTMLLink",array(
			"text" => $bean,
			"link" => $this->url . "/" . $bean . $this->queryString,
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
	function setQueryString($queryString) {
		$this->queryString = $queryString;
	}
}
