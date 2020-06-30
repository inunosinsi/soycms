<?php
/*
このブロックは、全てのブログページでご利用になれます。

ブログに設定されている、カテゴリ分けに使用するラベルの情報を出力します。

このブロックは、繰り返しブロックであり、該当するカテゴリーの個数だけブロックの内容が繰り返し出力されます。

ラベルの表示順が反映されます。

<ul>
<!-- b_block:id="category" -->
	<li><a cms:id="category_link">
			<!-- cms:id="category_name" --><!-- /cms:id="category_name" -->(<!-- cms:id="entry_count" -->0<!-- /cms:id="entry_count" -->)
		</a>
	</li>
<!-- /b_block:id="category" -->
</ul>
*/
function soy_cms_blog_output_category_link($page){

	if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");

	//ラベル一覧を取得：ラベルの表示順を反映する
	$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();//表示順に並んでいる
	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

	$blogLabelId = $page->page->getBlogLabelId();

	//カテゴリリンク
	$categories = $page->page->getCategoryLabelList();
	$categoryLabel = array();
	$entryCount = array();
	foreach($labels as $labelId => $label){
		if(in_array($labelId, $categories)){
			$categoryLabel[] =  $label;
			try{
				//記事の数を数える。
				$counts = $logic->getOpenEntryCountByLabelIds(array_unique(array($blogLabelId,$labelId)));
			}catch(Exception $e){
				$counts= 0;
			}
			$entryCount[$labelId] = $counts;
		}
	}

	$page->createAdd("category","CategoryListComponent",array(
		"list" => $categoryLabel,
		"entryCount" => $entryCount,
		"categoryUrl" => $page->getCategoryPageURL(true),
		"soy2prefix" => "b_block"
	));



}

/*
このブロックは、全てのブログページでご利用になれます。

投稿されている記事を、月別に集計し出力します。

このブロックは、繰り返しブロックであり、該当する月の個数だけブロックの内容が繰り返し出力されます。

<ul>
<!-- b_block:id="archive" -->
	<li><a cms:id="archive_link">
			<!-- cms:id="archive_month" cms:format="Y年m月" -->2012年1月<!-- /cms:id="archive_month" --> (<!-- cms:id="entry_count" -->0<!-- /cms:id="entry_count" -->)
		</a>
	</li>
<!-- /b_block:id="archive" -->
</ul>

 */
//readOnlyはクラスの定義のみ読み込みたい場合はtrue
function soy_cms_blog_output_archive_link($page, $readOnly = false){
	if(!$readOnly){
		//取得までできているので、整形や表示を設定する
		$month_list = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getCountMonth(array($page->page->getBlogLabelId()));
		foreach($month_list as $key => $month){
			if($month == 0){
				unset($month_list[$key]);
			}
		}

		if(!class_exists("MonthArciveListComponent")) SOY2::import("site_include.blog.component.MonthArciveListComponent");

		$page->createAdd("archive","MonthArciveListComponent",array(
			"list" => $month_list,
			"monthPageUri" => $page->getMonthPageURL(true),
			"secretMode" => true,
			"soy2prefix" => "b_block"
		));
	}
}

/*
このブロックは、全てのブログページでご利用になれます。

投稿されている記事を、年別に集計し出力します。

このブロックは、繰り返しブロックであり、該当する年の個数だけブロックの内容が繰り返し出力されます。

<ul>
<!-- b_block:id="archive_by_year" -->
	<li><a cms:id="archive_link">
			<!-- cms:id="archive_year" cms:format="Y年" --><!-- /cms:id="archive_year" --> (<!-- cms:id="entry_count" -->0<!-- /cms:id="entry_count" -->)
		</a>
	</li>
<!-- /b_block:id="archive_by_year" -->
</ul>

 */
function soy_cms_blog_output_archive_link_by_year($page){
	//取得までできているので、整形や表示を設定する
	$year_list = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getCountYear(array($page->page->getBlogLabelId()));
	foreach($year_list as $key => $count){
		if($count == 0){
			unset($year_list[$key]);
		}
	}

	if(!class_exists("YearArciveListComponent")) SOY2::import("site_include.blog.component.YearArciveListComponent");

	$page->createAdd("archive_by_year","YearArciveListComponent",array(
		"list" => $year_list,
		"yearPageUri" => $page->getMonthPageURL(true),
		"soy2prefix" => "b_block"
	));
}


function soy_cms_blog_output_archive_link_every_year($page){
	//取得までできているので、整形や表示を設定する
	$month_list = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getCountMonth(array($page->page->getBlogLabelId()));

	$month_every_year_list = array();
	foreach($month_list as $key => $month){
		if($month > 0){
			$month_every_year_list[date("Y", $key)][$key] = $month;
		}
	}

	if(!class_exists("MonthArciveListComponent")){
		soy_cms_blog_output_archive_link($page, true);
	}

	if(!class_exists("MonthArciveEveryYearListComponent")) SOY2::import("site_include.blog.component.MonthArciveEveryYearListComponent");

	$page->createAdd("archive_every_year", "MonthArciveEveryYearListComponent", array(
		"list" => $month_every_year_list,
		"monthPageUri" => $page->getMonthPageURL(true),
		"soy2prefix" => "b_block"
	));
}
