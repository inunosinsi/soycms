<?php

class SOYShop_SearchPageBase extends SOYShopPageBase{

	private $delegate;
	private $currentPage = 1;
	private $limit = 10;
	private $total = 0;


	function build($args){

		$page = $this->getPageObject();
		$obj = $page->getPageObject();

		$this->setLimit($obj->getDisplayCount());

		/**
		 * ページ数を取得
		 */
		if(count($args) > 0 && preg_match('/page-([0-9]+)[^0-9]*/', $args[count($args)-1], $tmp)){
			unset($args[count($args) - 1]);
			$args = array_values($args);
			$this->setCurrentPage($tmp[1]);

			//ページ部分の引数は取り除く
			$this->setArguments($args);
		}

		/**
		 * 検索モジュールの読み込み
		 */
		$plugin = soyshop_get_plugin_object($obj->getModule());
		if(!is_null($plugin->getId())){
			SOYShopPlugin::load("soyshop.search", $plugin);
			$delegate = SOYShopPlugin::invoke("soyshop.search", array(
				"page" => $page
			));
		}else{
			SOYShopPlugin::load("soyshop.search");
			$delegate = new SOYShopSearchModule();
		}

		/**
		 * 検索実行
		 */
		$items = $delegate->getItems($this->currentPage, $this->limit);
		$total = $delegate->getTotal();
		$this->setTotal($total);

		//item_list
		$this->createAdd("item_list","SOYShop_ItemListComponent", array(
			"list" => $items,
			"soy2prefix" => "block"
		));

		//form
		$this->addLabel("search_form", array(
			"html" => $delegate->getForm(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//if results exist
		$this->addModel("results", array(
			"visible" => ($total > 0),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//no_results
		$this->addModel("no_results", array(
			"visible" => ($total == 0),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//total
		$this->addLabel("total_count", array(
			"html" => $total,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$currentCount = ($this->getCurrentPage() - 1) * $this->getLimit();

		$this->addLabel("count_start", array(
			"html" => $currentCount + 1,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("count_end", array(
			"html" => $currentCount + $total,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->delegate = $delegate;
	}

	function getTotal() {
		return $this->total;
	}
	function setTotal($total) {
		$this->total = $total;
	}

	function getCurrentPage() {
		return $this->currentPage;
	}
	function setCurrentPage($currentPage) {
		$this->currentPage = (int)$currentPage;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}

	function getPager(){
		return new SOYShop_SearchPagePager($this);
	}

}

class SOYShop_SearchPagePager extends SOYShop_PagerBase{

	private $page;
	private $_pagerUrl;

	function __construct(SOYShop_SearchPageBase $page){
		$this->page = $page;
	}

	function getCurrentPage(){
		return $this->page->getCurrentPage();
	}

	function getTotalPage(){
		$page = ceil($this->page->getTotal() / $this->page->getLimit());
		return $page;
	}

	function getLimit(){
		return $this->page->getLimit();
	}

	function getPagerUrl(){
		if(!$this->_pagerUrl){
			$url = $this->page->getPageUrl(true);
			if($url[strlen($url)-1] == "/")$url = substr($url,0,strlen($url)-1);
			$this->_pagerUrl = $url;
		}
		return $this->_pagerUrl;
	}

	function getNextPageUrl(){
		$url = $this->getPagerUrl();
		$next = $this->getCurrentPage() + 1;
		$url .= "/page-" . $next . ".html?";

		$query = http_build_query($_GET);
		$url .= $query;

		return $url;
	}

	function getPrevPageUrl(){
		$url = $this->getPagerUrl();
		$prev = $this->getCurrentPage() - 1;
		$url .= "/page-" . $prev . ".html?";

		$query = http_build_query($_GET);
		$url .= $query;

		return $url;
	}

	function hasNext(){ return $this->getTotalPage() >= ($this->getCurrentPage() + 1); }
	function hasPrev(){ return ($this->getCurrentPage() - 1) > 0; }
}
