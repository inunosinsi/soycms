<?php

class PagerLogic extends SOY2LogicBase{

    private $pageURL;
	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $query;
	private $limit = 15;

	function setPageURL($value){
		$value = SOY2PageController::createLink($value);
		if($value[strlen($value)-1] != "/") $value .= "/";
		$this->pageURL = $value;
	}
	function setPublishPageUrl($value){
		$value = soyshop_get_mypage_url() . "/" . $value;
		if($value[strlen($value)-1] != "/") $value .= "/";
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
		return $this->pageURL . $this->page;
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
		if(strpos($this->pageURL, "?")){	//プラグインの詳細画面でページャを使用したい場合
			$pageURL = rtrim($this->pageURL, "/") . "&page=";
			$link = ($this->total > $this->end) ? $pageURL . ($this->page + 1) : $pageURL . $this->page;
		}else{
			$link = ($this->total > $this->end) ? $this->pageURL . ($this->page + 1) : $this->pageURL . $this->page;
		}
		if(strlen($this->getQuery()))$link .= "?" . $this->getQuery();

		return array(
    		"link" => $link,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : ""
    	);
	}
	function getPrevParam(){
		if(strpos($this->pageURL, "?")){	//プラグインの詳細画面でページャを使用したい場合
			$pageURL = rtrim($this->pageURL, "/") . "&page=";
			$link = ($this->page > 1) ? $pageURL . ($this->page - 1) : $pageURL . ($this->page);
		}else{
			$link = ($this->page > 1) ? $this->pageURL . ($this->page - 1) : $this->pageURL . ($this->page);
		}

		if(strlen($this->getQuery())) $link .= "?" . $this->getQuery();
		return array(
    		"link" => $link,
    		"class" => ($this->page <= 1) ? "pager_disable" : ""
    	);
	}
	function getPagerParam(){
    	$last_page = ceil($this->total / $this->limit);
    	$pagers = range(
    		max(1, min($this->page - 4, $last_page - 9)),
    		max(1, min($last_page, max(1, $this->page - 4) +9))
    	);

		return array(
    		"url" => $this->pageURL,
    		"current" => $this->page,
    		"list" => $pagers,
    		"query" => $this->query,
    	);
	}
	function getSelectArray(){
    	$pagers = range(1,$this->getLastPage());

		$array = array();
		foreach($pagers as $page){
			$array[ $page ] = $page;
		}

		return $array;
	}
	function getLastPage(){
		return $this->limit > 0 ? max(1, ceil($this->total / $this->limit)) : 1 ;
	}

	/**
	 * ページャー表示
	 *
	 * 使用例
	 * <!-- soy:id="count_start" /--> - <!-- soy:id="count_end" /--> of <!-- soy:id="count_max" /-->
	 * page <!-- soy:id="current_page" /--> / <!-- soy:id="last_page" /-->
	 * <!-- soy:id="pager_list" -->
	 *   <a href="" soy:id="target_link" >1</a>
	 * <!-- /soy:id="pager_list" -->
	 *
	 */
	function buildPager($htmlObj){

		$pager = $this;

		//件数情報表示
		$htmlObj->addLabel("count_start", array(
			"text" => $pager->getStart()
		));
		$htmlObj->addLabel("count_end", array(
			"text" => $pager->getEnd()
		));
		$htmlObj->addLabel("count_max", array(
			"text" => $pager->getTotal()
		));

		//ページ数
		$htmlObj->addLabel("current_page", array(
			"text" => $pager->getPage()
		));
		$htmlObj->addLabel("last_page", array(
			"text" => $pager->getLastPage()
		));

		//ページャの表示／非表示
		$htmlObj->addModel("has_multi_page", array(
			"visible" => ($pager->getLastPage() > 1)
		));
		$htmlObj->addModel("has_prev", array(
			"visible" => ($pager->getStart() != 1)
		));
		$htmlObj->addModel("has_next", array(
			"visible" => ($pager->getEnd() != $pager->getTotal())
		));

		//ページへのリンク
		$htmlObj->addLink("next_pager", $pager->getNextParam());
		$htmlObj->addLink("prev_pager", $pager->getPrevParam());
		$htmlObj->createAdd("pager_list", "SimplePager", $pager->getPagerParam());

		//ページへジャンプ
		$htmlObj->addForm("pager_jump", array(
			"method" => "get",
			"action" => $pager->getPageURL()
		));
		$htmlObj->addSelect("pager_select", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=$('#target_link_0').prop('href').substr(0, $('#target_link_0').prop('href').lastIndexOf('/')) + '/' + this.options[this.options.selectedIndex].value"
		));
	}

	function getQuery() {
		return $this->query;
	}
	function setQuery($query) {
		$this->query = $query;
	}
}
class SimplePager extends HTMLList{

	private $url;
	private $current;
	private $query;

	protected function populateItem($bean, $idx){
		if(strpos($this->url, "?")){	//プラグインの詳細画面でページャを使用したい場合
			$url = rtrim($this->url, "/") . "&page=" . $bean;
		}else{
			$url = $this->url . $bean;
		}
		if(strlen($this->query)) $url .= "?" . $this->query;

		$this->addLink("target_link", array(
			"text" => $bean,
			"link" => $url,
			"class" => ($this->current == $bean) ? "pager_current" : "",
			"id" => (is_numeric($idx)) ? "target_link_" . $idx : ""
		));
	}

	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		if($url[strlen($url)-1] != "/") $url .= "/";
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
