<?php

class PagerLogic {
	private $pageURL;
	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $limit = 15;
	
	function __construct(){
		
	}
	
	function setPageURL($value){
		if(strpos($value,"/")!==false){
			$this->pageURL = SOY2PageController::createRelativeLink($value);
		}else{
			$this->pageURL = SOY2PageController::createLink($value);
		}
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
			"soy2prefix" => "cms",
    		"link" => ($this->total > $this->end) ? $this->pageURL . "/" . ($this->page + 1) : $this->pageURL . "/" . $this->page,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : ""
    	);
	}
	function getPrevParam(){
		return array(
			"soy2prefix" => "cms",
    		"link" => ($this->page > 1) ? $this->pageURL . "/" . ($this->page - 1) : $this->pageURL . "/" . ($this->page),
    		"class" => ($this->page <= 1) ? "pager_disable" : ""
    	);
	}
	function getPagerParam(){
    	$pagers = range(
    		max(1, $this->page - 4),
    		min(ceil($this->total / $this->limit), max(1, $this->page - 4) +9)
    	);

		return array(
			"soy2prefix" => "cms",
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

class SimplePager extends HTMLList{
	
	private $url;
	private $current;
	
	protected function populateItem($bean){
		
		$this->createAdd("target_link","HTMLLink",array(
			"soy2prefix" => "cms",
			"html" => "$bean",
			"link" => $this->url . "/" . $bean,
			"class" => ($this->current == $bean) ? "pager_current" : ""		
		));
		
		$this->createAdd("target_get_link","HTMLLink",array(
			"soy2prefix" => "cms",
			"html" => "$bean",
			"link" => $this->url. "?page=" . $bean,
			"class" => ($this->current == $bean) ? "pager_current" : ""		
		));
		
//		$areaId = $_GET["area"][0];
//		$this->createAdd("target_mobile_link","HTMLLink",array(
//			"html" => "$bean",
//			"link" => $this->url. "/r?area[]=".$areaId."&page=" . $bean,
//			"class" => ($this->current == $bean) ? "pager_current" : ""		
//		));
		
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
}

?>