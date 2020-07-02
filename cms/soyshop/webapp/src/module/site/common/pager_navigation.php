<?php

function soyshop_pager_navigation($html, $page){

	$obj = $page->create("soyshop_pager_navigation", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_pager_navigation", $html)
	));

	if(!method_exists($page, "getPager")) return;

	$pager = $page->getPager();

	if(!$pager instanceof SOYShop_PagerBase) throw new Exception("getPager need return SOYShop_PagerBase");

	$current_page = $pager->getCurrentPage();

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

	$next_link = soyshop_add_get_value($pager->getNextPageUrl());
	$prev_link = soyshop_add_get_value($pager->getPrevPageUrl());

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
		"text" => $current_page,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	//total_page
	$obj->addLabel("total_page", array(
		"text" => $total_page,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$pageObject = $page->getPageObject();

	//商品一覧ページの場合
	if($pageObject->getType() == SOYShop_Page::TYPE_LIST || $pageObject->getType() == SOYShop_Page::TYPE_SEARCH){
		//登録されている商品数
		$total = (int)$page->getTotal();

		//1ページあたりの表示件数
		$limit = $pager->getLimit();

		//現在のページ番号
		$current = (int)$pager->getCurrentPage();

		//表示する最初の商品
		$start = ($current - 1) * $limit + 1;

		//表示する最後の商品（登録されている商品数を超えない）
		$end = min($current * $limit, $total);

		$obj->addLabel("start_item", array(
			"text" => $start,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$obj->addLabel("end_item", array(
			"text" => $end,
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
				if($total_page - $current > 9){
					$start_page = $current;
					$end_page = $current + 9;

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

		$obj->createAdd("paging_list", "PagingList", array(
			"soy2prefix" => "block",
			"list" => $pagingList,
			"url" => $url,
			"current" => $current
		));

		$obj->addLink("first_page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($url . "/page-1.html")
		));

		$obj->addLink("last_page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($url . "/page-" . $total_page . ".html")
		));
	}

	//詳細ページの場合
	if($pageObject->getType() == SOYShop_Page::TYPE_DETAIL){
		$nextItem = $page->getNextItem();
		$prevItem = $page->getPrevItem();

		$obj->addLabel("next_item_name", array(
			"text" => (!is_null($nextItem)) ? $nextItem->getName() : null,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"style" => ($hasNext) ? "" : "visibility:hidden;"
		));

		$obj->addLabel("prev_item_name", array(
			"text" => (!is_null($prevItem)) ? $prevItem->getName() : null,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"style" => ($hasPrev) ? "" : "visibility:hidden;"
		));
	}

	$obj->display();
}

class PagingList extends HTMLList{

	private $url;
	private $current;

	protected function populateItem($entity, $key){

		$this->addLink("page_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_add_get_value($this->url . "/page-" . $entity . ".html"),
			"text" => $entity,
			"class" => ($entity === $this->current) ? "page-link current-page" : "page-link",
		));
	}

	function setUrl($url){
		$this->url = $url;
	}
	function setCurrent($current){
		$this->current = $current;
	}
}
