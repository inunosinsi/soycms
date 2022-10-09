<?php

class JsonEntriesModulePagerLogic {
    private $pageURL;
    private $query;
    private $queryString;

    private $page = 1;
    private $start;
    private $end;
    private $total;
    private $limit = 15;
    
    function setPageURL($value){
        if(strpos($value,"/")!==false){
            $this->pageURL = SOY2PageController::createRelativeLink($value);
        }else{
            $this->pageURL = SOY2PageController::createLink($value);
        }
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

    function getHasNextOrPrevParam(){
        return array(
            "soy2prefix" => "cms",
            "visible" => ($this->total > $this->end || $this->page > 1),
        );
    }
    function getHasNextParam(){
        return array(
            "soy2prefix" => "cms",
            "visible" => ($this->total > $this->end),
        );
    }
    function getHasPrevParam(){
        return array(
            "soy2prefix" => "cms",
            "visible" => ($this->page > 1),
        );
    }
    function getNextParam(){
        $query = $this->queryString;
        // if($this->total > $this->end){
        //     if(strlen($query)){
        //         $query .= "&".$this->pagerParamKey."=".($this->page + 1);
        //     }else{
        //         $query  = "?".$this->pagerParamKey."=".($this->page + 1);
        //     }
        // }
        return array(
            "soy2prefix" => "cms",
            "link"	=> $this->pageURL . $query,
            "class"   => ($this->total <= $this->end) ? "pager_disable" : "",
            "visible" => ($this->total >  $this->end),
        );
    }
    function getPrevParam(){
        $query = $this->queryString;
        if($this->page > 2){
            if(strlen($query)){
                $query .= "&".$this->pagerParamKey."=".($this->page - 1);
            }else{
                $query  = "?".$this->pagerParamKey."=".($this->page - 1);
            }
        }
        return array(
            "soy2prefix" => "cms",
            "link"	=> $this->pageURL . $query,
            "class"   => ($this->page <= 1) ? "pager_disable" : "",
            "visible" => ($this->page > 1),
        );
    }
    function getPagerParam(){
        $pagers = $this->limit ? range(
            max(1, $this->page - 4),
            max(1, min(ceil($this->total / $this->limit), max(1, $this->page - 4) +9))
        ) : array() ;

        return array(
            "soy2prefix" => "cms",
            "url" => $this->pageURL,
            "queryString" => $this->queryString,
            "current" => $this->page,
            "list" => $pagers
        );
    }
    function getSelectArray(){
        $pagers = $this->limit ? range(
            1,
            (int)($this->total / $this->limit) + 1
        ) : array() ;

        $array = array();
        foreach($pagers as $page){
//			$array[ $this->pageURL."/".$page . $this->queryString ] = $page;
            $array[ $page ] = $page;
        }

        return $array;
    }
}

class JsonEntriesModuleSimplePager extends HTMLList{

    private $url;
    private $queryString;
    private $current;
    
    protected function populateItem($bean){

        $this->addLink("target_link", array(
            "soy2prefix" => "cms",
            "html" => "$bean",
            "link" => $this->url . ( $bean > 1 ? "/" . $bean : "" ) . $this->queryString,
            "class" => ($this->current == $bean) ? "pager_current" : ""
        ));
        $this->addModel("target_wrapper",array(
            "soy2prefix" => "cms",
            "class" => ($this->current == $bean) ? "act" : ""
        ));

        $query = $this->queryString;
        if($bean > 1){
            // if(strlen($query)){
            //     $query .= "&".$this->pagerParamKey."=".$bean;
            // }else{
            //     $query  = "?".$this->pagerParamKey."=".$bean;
            // }
        }
        $this->addLink("target_get_link", array(
            "soy2prefix" => "cms",
            "html" => "$bean",
            "link" => $this->url.$query,
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