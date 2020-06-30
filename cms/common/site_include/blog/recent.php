<?php

/*
このブロックは、全てのブログページでご利用になれます。

最近投稿された記事一覧を出力します。

このブロックは、繰り返しブロックであり、該当する記事の個数だけブロックの内容が繰り返し出力されます。

ここで表示される件数は、設定ページより設定できるRSSページの表示件数と同一です。

<ul>
<!-- b_block:id="recent_entry_list" -->
	<li>
		<a cms:id="entry_link">
			<!-- cms:id="title" -->ここにタイトルが入ります<!-- /cms:id="title" -->(<!-- cms:id="create_date" cms:format="m/i"-->03/17<!-- /cms:id="create_date" -->)
		</a>
	</li>
<!--/b_block:id="recent_entry_list" -->
</ul>
*/
function soy_cms_blog_output_recent_entry_list($page, $entries){

	if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");
	if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");

	$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
	$entryLogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

	$categoryLabel = array();
	$entryCount = array();
	foreach($labels as $labelId => $label){
		if(in_array($labelId, $page->page->getCategoryLabelList())){
			$categoryLabel[] =  $label;
			try{
				//記事の数を数える。
				$counts = $entryLogic->getOpenEntryCountByLabelIds(array_unique(array((int)$page->page->getBlogLabelId(),$labelId)));
			}catch(Exception $e){
				$counts= 0;
			}
			$entryCount[$labelId] = $counts;
		}
	}

	$page->createAdd("recent_entry_list","EntryListComponent",array(
		"list" => $entries,
		"entryPageUrl"=> $page->getEntryPageURL(true),
		"categoryPageUrl" => $page->getCategoryPageURL(true),
		"blogLabelId" => $page->page->getBlogLabelId(),
		"categoryLabelList" => $page->page->getCategoryLabelList(),
		"entryCount" => $entryCount,
		"soy2prefix" => "b_block"
	));

}

/*
このブロックは、全てのブログページでご利用になれます。

最近投稿されたコメント一覧を出力します。

このブロックは、繰り返しブロックであり、該当するコメントの個数だけブロックの内容が繰り返し出力されます。

ここで表示される件数は、現在は10件で固定となっております。

<ul>
<!-- b_block:id="recent_comment_list" -->
<li>
	<a cms:id="entry_link">
		<!-- cms:id="title" -->コメントのタイトル<!-- /cms:id="title" -->
		<br />=>
		<!-- cms:id="entry_title" -->記事のタイトル<!-- /cms:id="entry_title" -->
		[<!-- cms:id="submit_date" cms:format="m/d" -->03/17<!-- /cms:id="submit_date" -->]
	</a>
</li>
<!--/b_block:id="recent_comment_list" -->
</ul>
*/
function soy_cms_blog_output_recent_comment_list($page){

	if(!class_exists("RecentCommentListComponent")) SOY2::import("site_include.blog.component.RecentCommentListComponent");

	$page->createAdd("recent_comment_list","RecentCommentListComponent",array(
		"list" => SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic")->getRecentComments(array($page->page->getBlogLabelId())),
		"entryPageUri" => $page->getEntryPageURL(true),
		"soy2prefix" => "b_block"
	));
}

/*
このブロックは、全てのブログページでご利用になれます。

最近投稿されたトラックバック一覧を出力します。

このブロックは、繰り返しブロックであり、該当するトラックバックの個数だけブロックの内容が繰り返し出力されます。

ここで表示される件数は、現在は10件で固定となっております。

<ul>
<!-- b_block:id="recent_trackback_list" -->
	<li>
		<a cms:id="entry_link">
			<!-- cms:id="title" -->タイトル<!-- /cms:id="title" -->
			<br />=><!-- cms:id="entry_title" -->記事のタイトル<!-- /cms:id="entry_title" -->
			[<!-- cms:id="submit_date" cms:format="m/d" -->03/17<!-- /cms:id="submit_date" -->]
		</a>
	</li>
<!--/b_block:id="recent_trackback_list" -->
</ul>
*/
function soy_cms_blog_output_recent_trackback_list($page){

	if(!class_exists("RecentTrackBackListComponent")) SOY2::import("site_include.blog.component.RecentTrackBackListComponent");
	$page->createAdd("recent_trackback_list","RecentTrackBackListComponent",array(
		"list" => SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic")->getRecentTrackbacks(array($page->page->getBlogLabelId())),
		"entryPageUri" => $page->getEntryPageURL(true),
		"soy2prefix" => "b_block"
	));

}
