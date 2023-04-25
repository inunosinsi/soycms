<?php
function soycms_blogparts(string $html, HTMLPage $page){

	$obj = $page->create("blogparts", "HTMLTemplatePage", array(
		"arguments" => array("blogparts", $html)
	));

	if(property_exists($page, "page")){
		switch($page->page->getPageType()){
		case Page::PAGE_TYPE_BLOG:
			switch(SOYCMS_BLOG_PAGE_MODE){
			case "_top_":
				$template = $page->page->getTopTemplate();
				break;
			case "_entry_":
				$template = $page->page->getEntryTemplate();
				break;
			default:
				$template = $page->page->getArchiveTemplate();
			}
			break;
		default:
			$template = $page->page->getTemplate();
		}
	//SOY Shopの場合
	}else{
		switch(get_class($page)){
			case "SOYShop_UserPage":
				$template = file_get_contents(SOYSHOP_SITE_URL . ".template/mypage/" . $page->getMyPageId() . ".html");
				break;
			case "SOYShop_CartPage":
				$template = file_get_contents(SOYSHOP_SITE_URL . ".template/cart/" . $page->getCartId() . ".html");
				break;
			default:
				$pageObject = $page->getPageObject();
				$template = file_get_contents(SOYSHOP_SITE_URL . ".template/" . $pageObject->getType() . "/" . $pageObject->getTemplate());
		}
	}


	$blogPageId = null;
	if(preg_match('/(<[^>]*[^\/]cms:module=\"common.blogparts\"[^>]*>)/', $template, $tmp)){
		if(preg_match('/cms:blog=\"(.*?)\"/', $tmp[1], $ctmp)){
			if(isset($ctmp[1]) && is_numeric($ctmp[1])) $blogPageId = (int)$ctmp[1];
		}
	}

	if(is_null($blogPageId)){
		//最初に作成されたブログのラベルIDを取得する
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id FROM Page WHERE page_type = 200 ORDER BY id ASC LIMIT 1;");
			if(isset($res[0]["id"])) $blogPageId =	(int)$res[0]["id"];
		}catch(Exception $e){
			//
		}
		unset($dao);
	}

	//ブログページ
	$blog = soycms_get_hash_table_dao("blog_page")->getById($blogPageId);

	//b_block:id="category"
	$labelDao = soycms_get_hash_table_dao("label");
	$labels = $labelDao->get();//表示順に並んでいる

	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

	$blogLabelId = $blog->getBlogLabelId();

	//カテゴリリンク
	$categories = $blog->getCategoryLabelList();
	$categoryLabel = array();
	$entryCount = array();
	foreach($labels as $labelId => $label){
		if(in_array($labelId, $categories)){
			$categoryLabel[] =	$label;
			try{
				//記事の数を数える。
				$count = $logic->getOpenEntryCountByLabelIds(array_unique(array($blogLabelId,$labelId)));
			}catch(Exception $e){
				$count= 0;
			}
			$entryCount[$labelId] = $count;
		}
	}

	if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");
	$obj->createAdd("category", "CategoryListComponent", array(
		"list" => $categoryLabel,
		"entryCount" => $entryCount,
		"categoryUrl" => convertUrlOnModuleBlogParts($blog->getCategoryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="archive"
	$labels = array($blog->getBlogLabelId());

	//取得までできているので、整形や表示を設定する
	$month_list = $logic->getCountMonth($labels);

	foreach($month_list as $key => $month){
		if($month > 0) continue;
		unset($month_list[$key]);
	}

	if(!class_exists("MonthArciveListComponent")) SOY2::import("site_include.blog.component.MonthArciveListComponent");
	$obj->createAdd("archive","MonthArciveListComponent",array(
		"list" => $month_list,
		"monthPageUri" => convertUrlOnModuleBlogParts($blog->getMonthPageURL(true)),
		"secretMode" => true,
		"soy2prefix" => "b_block"
	));

	//b_block:id="archive_every_year"
	$month_every_year_list = array();
	foreach($month_list as $key => $month){
		if($month <= 0) continue;
		$month_every_year_list[date("Y", $key)][$key] = $month;
	}

	if(!class_exists("MonthArciveEveryYearListComponent")) SOY2::import("site_include.blog.component.MonthArciveEveryYearListComponent");
	$obj->createAdd("archive_every_year", "MonthArciveEveryYearListComponent", array(
		"list" => $month_every_year_list,
		"monthPageUri" => convertUrlOnModuleBlogParts($blog->getMonthPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="recent_entry_list"
	$logic->setLimit($blog->getRssDisplayCount());
	$logic->setOffset(0);

	if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");
	$obj->createAdd("recent_entry_list","EntryListComponent",array(
		"list" => $logic->getOpenEntryByLabelIds(array($blogLabelId)),
		"entryPageUrl" => convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="recent_comment_list"
	try{
		$comments = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic")->getRecentComments(array($blogLabelId));
	}catch(Exception $e){
		$comments = array();
	}

	if(!class_exists("RecentCommentListComponent")) SOY2::import("site_include.blog.component.RecentCommentListComponent");
	$obj->createAdd("recent_comment_list","RecentCommentListComponent",array(
		"list" => $comments,
		"entryPageUri" => convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	try{
		$trackbacks = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic")->getRecentTrackbacks(array($blogLabelId));
	}catch(Exception $e){
		$trackbacks = array();
	}

	if(!class_exists("RecentTrackBackListComponent")) SOY2::import("site_include.blog.component.RecentTrackBackListComponent");
	$obj->createAdd("recent_trackback_list","RecentTrackBackListComponent",array(
		"list" => $trackbacks,
		"entryPageUri" => convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	$obj->display();
}

if(!function_exists("convertUrlOnModuleBlogParts")){
	/**
	 * @param string
	 * @return string
	 */
	function convertUrlOnModuleBlogParts(string $url){
		static $siteUrl;
		if(is_null($siteUrl)){
			$siteUrl = "/";
			if(!SOYCMS_IS_DOCUMENT_ROOT) $siteUrl .= SOYCMS_SITE_ID . "/";
		}
		return $siteUrl . $url;
	}
}