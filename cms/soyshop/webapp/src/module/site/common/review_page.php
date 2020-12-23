<?php

function soyshop_review_page($html, $htmlObj){
	$obj = $htmlObj->create("soyshop_review_page", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_review_page", $html)
	));

	$args = $htmlObj->getArguments();
	$itemId = (isset($args[0])) ? (int)$args[0] : null;

	SOY2::import("util.SOYShopPluginUtil");
	if(is_numeric($itemId) && SOYShopPluginUtil::checkIsActive("item_review")){
		$item = soyshop_get_item_object($itemId);

		$obj->addLink("back_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_get_item_detail_link($item)
		));

		$obj->addLabel("review_item_name", array(
			"soy2prefix" =>SOYSHOP_SITE_PREFIX,
			"text" => $item->getOpenItemName()
		));

		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
		$config = ItemReviewUtil::getConfig();

		$limit = (isset($config["review_count"]) && is_numeric($config["review_count"])) ? (int)$config["review_count"] : 15;

		//現在のページ
		if(count($args) > 0 && preg_match('/page-([0-9]+)[^0-9]*/', $args[count($args)-1], $tmp)){
			unset($args[count($args) - 1]);
            $args = array_values($args);
			$currentPage = (isset($tmp[1]) && is_numeric($tmp[1])) ? (int)$tmp[1] : 1;
		}else{
			$currentPage = 1;
		}

		$offset = $limit * ($currentPage - 1);

		$searchLogic = SOY2Logic::createInstance("module.plugins.item_review.logic.SearchReviewAdvanceLogic");
		$searchLogic->setItemId($itemId);
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$reviews = $searchLogic->search();

		SOY2::import("module.plugins.item_review.component.ReviewsListComponent");
		$obj->createAdd("review_list", "ReviewsListComponent", array(
			"soy2prefix" => "block",
			"list" => $reviews,
			"itemId" => 0,
		));

		$total = $searchLogic->getTotal();

		//total < current * limitの場合は404にリダイレクトする
        if($total > 0 && $total <= (int)$limit * ((int)$currentPage - 1)) {
            SOY2PageController::redirect(soyshop_get_site_url(true) . SOYSHOP_404_PAGE_MARKER);
        }

		//ページャ
		$page = $htmlObj->getPageObject();
		$pager = new SOYShop_ReviewPagePager($page);
		$pager->setArgs($args);
		$pager->setCurrentPage($currentPage);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$hasNext = $pager->hasNext();
		$hasPrev = $pager->hasPrev();

		$url = $pager->getPagerUrl();	//always end not slash

		$total_page = $pager->getTotalPage();

		$obj->addModel("has_pager", array(
			"soy2prefix" => "block",
			"visible" => ($total_page > 1)
		));
		$obj->addModel("no_pager", array(
			"soy2prefix" => "block",
			"visible" => ($total_page < 2)
		));

		$next_link = $pager->getNextPageUrl();
		$prev_link = $pager->getPrevPageUrl();

		$pager->execute();

		$obj->addModel("no_next", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => !$hasNext
		));

		$obj->addModel("has_next", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => $hasNext
		));

		//next_page_link
		$obj->addLink("next_link", array(
			"link" => $next_link,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"style" => ($hasNext) ? "" : "visibility:hidden;"
		));

		$obj->addModel("no_prev", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => !$hasPrev
		));

		$obj->addModel("has_prev", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => $hasPrev
		));

		//prev_page_link
		$obj->addLink("prev_link", array(
			"link" => $prev_link,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"style" => ($hasPrev) ? "" : "visibility:hidden;"
		));

		//current_page
		$obj->addLabel("current_page", array(
			"text" => $currentPage,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//total_page
		$obj->addLabel("total_page", array(
			"text" => $total_page,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$obj->addLabel("start_item", array(
			"text" => ($currentPage - 1) * $limit + 1,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$obj->addLabel("end_item", array(
			"text" => min($currentPage * $limit, $total),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$obj->addLabel("total_item", array(
			"text" => $total,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//リンクのリスト式ページャの表示
		$pagingList = array();
		if($total_page > 0){
			if($total_page > 10){

				//数あるページの最中のページを表示した場合はcurrentから10個分のリンクを表示する
				if($total_page - $currentPage > 9){
					$start_page = $currentPage;
					$end_page = $currentPage + 9;

				//数あるページの最後の方のページを表示した場合は、最後のページから前10個分のリンクを表示する
				}else{
					$start_page = $total_page - 9;
					$end_page = $total_page;
				}

			//ページ数が10以下の時は常に1～表示する
			}else{
				$start_page = 1;
				$end_page = $total_page;
			}

			for($j = $start_page; $j <= $end_page; $j++){
				$pagingList[] = (int)$j;
			}
		}

		$obj->createAdd("paging_list", "ReviewPagingList", array(
			"soy2prefix" => "block",
			"list" => $pagingList,
			"url" => $url,
			"current" => $currentPage
		));

		$obj->addLink("first_page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($url . "/page-1.html")
		));

		$obj->addLink("last_page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($url . "/page-" . $total_page . ".html")
		));

		$obj->display();
	}else{
		echo "レビューページを開く際にエラーがありました";
	}
}

SOY2::import("base.site.SOYShopPageBase");
class SOYShop_ReviewPagePager extends SOYShop_PagerBase{

	private $page;
	private $args;
	private $currentPage;
	private $total;
	private $limit;

    function __construct(SOYShop_Page $page){
		$this->page = $page;
	}

    function getTotalPage(){
        return max(1, ceil($this->total / $this->limit));
    }

    private $_pagerUrl;

    function getPagerUrl(){
        if(!$this->_pagerUrl){
			$url = soyshop_get_page_url($this->page->getUri());
			$url .= "/" . implode("/", $this->args);
            if($url[strlen($url) - 1] == "/")$url = substr($url, 0, strlen($url) - 1);
            $this->_pagerUrl = $url;
        }
        if(strpos($this->_pagerUrl, "/" . SOYShop_Page::URI_HOME)){
            $this->_pagerUrl = str_replace("/" . SOYShop_Page::URI_HOME, "", $this->_pagerUrl);
        }
        if(strpos($this->_pagerUrl, "/" . SOYShop_Page::NOT_FOUND)){
            $this->_pagerUrl = str_replace("/" . SOYShop_Page::NOT_FOUND, "", $this->_pagerUrl);
        }
        return $this->_pagerUrl;
    }

    function getNextPageUrl(){
        $url = $this->getPagerUrl();
        $next = $this->currentPage + 1;
        return $url . "/page-" . $next . ".html";
    }

    function getPrevPageUrl(){
        $url = $this->getPagerUrl();
        $prev = $this->currentPage - 1;
        if($prev < 0){
            return "";
        }elseif($prev == 0){
            return $url;
        }else{
            return $url . "/page-" . $prev . ".html";
        }
    }

    function hasNext(){ return $this->getTotalPage() >= ($this->currentPage + 1); }
    function hasPrev(){ return ($this->currentPage - 1) > 0; }

	function setArgs($args){
		$this->args = $args;
	}
	function setCurrentPage($currentPage){
		$this->currentPage = $currentPage;
	}
	function setTotal($total){
		$this->total = $total;
	}
	function setLimit($limit){
		$this->limit = $limit;
	}
}


class ReviewPagingList extends HTMLList{

	private $url;
	private $current;

	protected function populateItem($entity, $key){

		$this->addLink("page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($this->url . "/page-" . $entity . ".html"),
			"text" => $entity,
			"class" => ($entity === $this->current) ? "current-page" : "",
		));
	}

	function setUrl($url){
		$this->url = $url;
	}
	function setCurrent($current){
		$this->current = $current;
	}
}
