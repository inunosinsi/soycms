<?php
/*
このブロックは、記事毎ページでご利用いただけます。

記事毎ページの該当記事の内容を出力する際に用いることができます。

<!-- b_block:id="entry" -->
	<div>
		<h2 cms:id="title">ここにはタイトルが入ります。</h2>
			<span cms:id="create_date" cms:format="Y/m/d">2008/03/17</span>
		<div cms:id="content">ここには本文が入ります</div cms:id="content">
		<div cms:id="more">ここには追記が入ります。</div cms:id="more">
			<span cms:id="create_time" cms:format="H:i">17:00</span>
			<a cms:id="comment_link">コメント(<!-- cms:id="comment_count"--><!-- /cms:id="comment_count"-->)</a>
			<a cms:id="trackback_link">トラックバック(<!-- cms:id="trackback_count"--><!-- /cms:id="trackback_count"-->)</a>
		<p>
			<!-- cms:id="category_list" -->
				<a cms:id="category_link"><!-- cms:id="category_name" --><!-- /cms:id="category_name" --></a>
			<!-- /cms:id="category_list" -->
		</p>
	</div>
<!-- /b_block:id="entry" -->
*/
/**
 * 記事の詳細情報を出力します。
 *
 */
function soy_cms_blog_output_entry($page,$entry){

	if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");
	if(!class_exists("PagerListComponent")) SOY2::import("site_include.blog.component.PagerListComponent");
	if(!class_exists("EntryComponent")) SOY2::import("site_include.blog.component.EntryComponent");

	$page->createAdd("entry","EntryComponent",array(
		"soy2prefix" => "b_block",
		"entryPageUri"=> $page->getEntryPageURL(true),
		"categoryPageUri" => $page->getCategoryPageURL(true),
		"blogLabelId" => $page->page->getBlogLabelId(),
		"categoryLabelList" => $page->page->getCategoryLabelList(),
		"labels" => SOY2DAOFactory::create("cms.LabelDAO")->get(),
		"entryLogic" => SOY2Logic::createInstance("logic.site.Entry.EntryLogic"),
		"visible" => ($entry->getId()),
		"entry" => $entry
	));
}

/**
 * 次の記事を出力
 * 次の記事が無い場合は表示されない
 *
 * <div b_block:id="next_entry">
 * 	<a cms:id="entry_link"><!-- cms:id="title" --><!--/cms:id="title" --></a>
 * </div b_block:id="next_entry">
 *
 * <div b_block:id="prev_entry">
 * 	<a cms:id="entry_link"><!-- cms:id="title" --><!--/cms:id="title" --></a>
 * </div b_block:id="prev_entry">
 */
function soy_cms_blog_output_entry_navi($page,$next,$prev){

	if(!class_exists("EntryNavigationComponent")) SOY2::import("site_include.blog.component.EntryNavigationComponent");

	$page->createAdd("next_entry","EntryNavigationComponent",array(
		"entryPageUri"=> $page->getEntryPageURL(true),
		"entry" => $next,
		"soy2prefix" => "b_block",
		"visible" => $next->getId()
	));

	$page->createAdd("prev_entry","EntryNavigationComponent",array(
		"entryPageUri"=> $page->getEntryPageURL(true),
		"entry" => $prev,
		"soy2prefix" => "b_block",
		"visible" => $prev->getId()
	));
}


/*
このブロックは、記事毎ページでご利用になれます。

このブロックで、記事に対して、閲覧者がコメントを投稿できるようにフォームを設置することができます。

ここで投稿されたコメント、管理ページより確認することができます。

このブロックは必ずFORMタグに使用してください。

<form b_block:id="comment_form">
	<p>タイトル：<input cms:id="title" /></p>
	<p>お名前：<input cms:id="author" /></p>
	<p>mail：<input cms:id="mail_address" /></p>
	<p>URL：<input cms:id="url" /></p>
	<p><textarea cms:id="body"></textarea></p>
	<input type="submit" value="投稿">
</form b_block:id="comment_form">
 */
function soy_cms_blog_output_comment_form($page,$entry,$entryComment){

	if(!class_exists("CommentFormComponent")) SOY2::import("site_include.blog.component.CommentFormComponent");
	$page->createAdd("comment_form","CommentFormComponent",array(
		"action" => $page->getEntryPageURL(true) . $entry->getId() ."?comment",
		"soy2prefix" => "b_block",
		"entryComment" => (is_null($entryComment)) ? new EntryComment() : $entryComment,
		"visible" => ($entry->getId())
	));
}

/*
このブロックは、記事毎ページでご利用になれます。

このブロックで、記事に対して、投稿されたコメントの一覧を出力させることができます。

管理ページより、拒否に設定されているコメントは表示されません。


<!-- b_block:id="comment_list" -->
<div>
	<h5 cms:id="title" cms:alt="無題">タイトル</h5>
	<a cms:id="mail_address"><!-- cms:id="author" cms:alt="名無しさん" -->名前<!-- /cms:id="author" --></a>
	|<!-- cms:id="submit_date" cms:format="Y-m-d"-->2008-03-17<!-- /cms:id="submit_date"-->
	|<a cms:id="url">URL</a>
	<div cms:id="body" cms:alt="本文無し">本文</div>
	<span cms:id="submit_time" cms:format="H:i">17:52</span>
</div>
<!--/b_block:id="comment_list" -->

*/
function soy_cms_blog_output_comment_list($page,$entry){

	if(!class_exists("CommentListComponent")) SOY2::import("site_include.blog.component.CommentListComponent");

	$commentList = SOY2DAOFactory::create("cms.EntryCommentDAO")->getApprovedCommentByEntryId($entry->getId());

	$page->createAdd("comment_list","CommentListComponent",array(
		"list" => $commentList,
		"soy2prefix" => "b_block",
		"visible" => ($entry->getId())
	));

	$page->addModel("has_comment",array(
		"visible" => count($commentList),
		"soy2prefix" => "b_block",
	));
}

/*
このブロックは、記事毎ページでご利用になれます。

このブロックで、記事に対して、投稿されたトラックバックの一覧を出力させることができます。

管理ページより、拒否に設定されているトラックバックは表示されません。

投稿されたトラックバックは初期状態は拒否になっているため、許可に設定しなければ表示されないことにご注意ください。

<ul>
<!-- b_block:id="trackback_list" -->
	<li>
		<h5 cms:id="title">タイトル</h5>
		<a cms:id="url"><!-- cms:id="blog_name"-->ブログ名<!-- /cms:id="blog_name"--></a>
		<p cms:id="excerpt">要約</p>
		<span cms:id="submit_date" cms:format="Y/m/d H:i">2008/03/15 12:35</span>
	</li>
<!--/b_block:id="trackback_list" -->
</ul>
*/
function soy_cms_blog_output_trackback_list($page,$entry){

	if(!class_exists("TrackbackListComponent")) SOY2::import("site_include.blog.component.TrackbackListComponent");

	$trackbackList = SOY2DAOFactory::create("cms.EntryTrackbackDAO")->getCertificatedTrackbackByEntryId($entry->getId());

	$page->createAdd("trackback_list","TrackbackListComponent",array(
		"list" => $trackbackList,
		"soy2prefix" => "b_block",
		"visible" => ($entry->getId())
	));

	$page->addModel("has_trackback",array(
		"visible" => count($trackbackList),
		"soy2prefix" => "b_block",
	));
}


/*
このブロックは、記事毎ページでご利用になれます。

このブロックで、この記事に投稿するためのURLを出力します。

このブロックは必ずINPUTタグにご使用ください。

<input b_block:id="trackback_link">
 */
function soy_cms_blog_output_trackback_link($page,$entry){

	/**
	 * 絶対URL（http://～）
	 */
	$trackbackUrl = $page->getEntryPageURL(true) . $entry->getId() ."?trackback";

	if(!class_exists("TrackbackURLComponent")) SOY2::import("site_include.blog.component.TrackbackURLComponent");
	$page->createAdd("trackback_link","TrackbackURLComponent",array(
		"value" => $trackbackUrl,
		"text" => $trackbackUrl,
		"soy2prefix" => "b_block",
		"type"=>"text"
	));
}
