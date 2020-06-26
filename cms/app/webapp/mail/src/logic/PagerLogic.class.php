<?php

class PagerLogic {
	private $pageURL;
	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $limit = 15;
		
	function setPageURL($value){
		$this->pageURL = SOY2PageController::createLink($value);
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
    		"link" => ($this->total > $this->end) ? $this->pageURL . "/" . ($this->page + 1) : $this->pageURL . "/" . $this->page,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : ""
    	);
	}
	function getPrevParam(){
		return array(
    		"link" => ($this->page > 1) ? $this->pageURL . "/" . ($this->page - 1) : $this->pageURL . "/" . ($this->page),
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
//			$array[ $this->pageURL."/".$page ] = $page;
			$array[ $page ] = $page;
		}
		
		return $array;
	}
}
?>