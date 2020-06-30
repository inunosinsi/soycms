<?php

/*
このブロックは、アーカーブページ（月別アーカイブページおよびカテゴリーページ）でご利用になれます。

現在表示している年月またはカテゴリー名を出力します。

1.2.4～

<!-- b_block:id="current_category_or_archive" -->
    <h2><a cms:id="archive_link"><!-- cms:id="archive_name" cms:format="Y年m月" -->年月またはカテゴリー名<!-- /cms:id="archive_name" --></a></h2>
<!-- /b_block:id="current_category_or_archive" -->

1.3.4～
DateLabelで %Y:xxx%, %M:xxx%, %D:xxx% を使えるようになりました。
cms:format="%Y:Y年%%M:n月%%D:j日%" とすると、URLに応じて2010年、2010年12月、2010年12月15日のように切り替わります。
<!-- b_block:id="current_category_or_archive" -->
    <h2><a cms:id="archive_link"><!-- cms:id="archive_name" cms:format="%Y:Y年%%M:-n%%D:-j%" /--></a></h2>
<!-- /b_block:id="current_category_or_archive" -->


 */
function soy_cms_blog_output_current_category_or_archive($page){

    if(!class_exists("CurrentCategoryOrArchiveComponent")) SOY2::import("site_include.blog.component.CurrentCategoryOrArchiveComponent");
    $page->createAdd("current_category_or_archive","CurrentCategoryOrArchiveComponent",array(
        "soy2prefix"=>"b_block",
        "page"=>$page,
    ));
}

/*
このブロックは、カテゴリーページでご利用になれます。

現在表示しているカテゴリー名を出力します。

また、月別アーカイブページと同一テンプレートですが、月別アーカイブページでは、このブロックは表示されません。

<!-- b_block:id="current_category" -->
    <h2><a cms:id="category_link"><!-- cms:id="category_name" -->カテゴリーのタイトル<!-- /cms:id="category_name"--></a></h2>
<!-- /b_block:id="current_category" -->

1.8.6～
<!-- b_block:id="is_category_page" -->
カテゴリーページでのみ表示したい内容
<!-- /b_block:id="is_category_page" -->
 */
function soy_cms_blog_output_current_category($page){

    if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");

    $page->createAdd("current_category","CategoryListComponent",array(
        "soy2prefix"=>"b_block",
        "list" => (!is_null($page->label) && $page->label instanceof Label) ? array($page->label) : array(),
		"categoryUrl" => $page->getCategoryPageURL(true),
        "visible" => ($page->mode==CMSBlogPage::MODE_CATEGORY_ARCHIVE)
    ));

    $page->addModel("is_category_page", array(
        "soy2prefix"=>"b_block",
        "visible"=>($page->mode==CMSBlogPage::MODE_CATEGORY_ARCHIVE)
    ));
}


/*
このブロックは、アーカイブページでご利用になれます。

現在表示している年月または年月日を出力します。

また、月別アーカイブページと同一テンプレートですが、カテゴリーアーカイブページでは、このブロックは表示されません。

1.2.2～

<!-- b_block:id="current_archive" -->
    <h2><a cms:id="archive_link"><!-- cms:id="archive_month" cms:format="Y年m月" --><!-- /cms:id="archive_month" --></a></h2>
<!-- /b_block:id="current_archive" -->

<!-- b_block:id="current_archive_date" -->
    <h2><a cms:id="archive_link"><!-- cms:id="archive_date" cms:format="Y年m月d日" --><!-- /cms:id="archive_date" --></a></h2>
<!-- /b_block:id="current_archive_date" -->


1.3.4～
current_archive_ymdが追加されました。current_archive_ymdはcurrent_archive, current_archive_dateと異なり、日付指定の有無にかかわらず常に表示されます。
DateLabelで %Y:xxx%, %M:xxx%, %D:xxx% を使えるようになりました。
cms:format="%Y:Y年%%M:n月%%D:j日%" とすると、URLに応じて2010年、2010年12月、2010年12月15日のように切り替わります。
<!-- b_block:id="current_archive_ymd" -->
    <h2><a cms:id="archive_link"><!-- cms:id="archive_date" cms:format="%Y:Y年%%M:-n%%D:-j%" /--></a></h2>
<!-- /b_block:id="current_archive_ymd" -->

1.8.6～
<!-- b_block:id="is_archive_page" -->
アーカイブページでのみ表示したい内容
<!-- /b_block:id="is_archive_page" -->
 */
function soy_cms_blog_output_current_archive($page){

    if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");

	$label = (!is_null($page->label) && $page->label instanceof Label) ? $page->label : new Label();
	$isArchive = ($page->mode==CMSBlogPage::MODE_MONTH_ARCHIVE);
    $page->createAdd("current_archive","CategoryListComponent",array(
        "soy2prefix"=>"b_block",
        "list"=> array($label),
		"visible"=>($isArchive && !$page->day)
    ));

    $page->createAdd("current_archive_date","CategoryListComponent",array(
        "soy2prefix"=>"b_block",
        "list"=> array($label),
        "visible"=>($isArchive && $page->day)
    ));

    $page->createAdd("current_archive_ymd","CategoryListComponent",array(
        "soy2prefix"=>"b_block",
        "list" => array($label),
        "visible"=>$isArchive
    ));

    $page->addModel("is_archive_page", array(
        "soy2prefix" => "b_block",
        "visible" => $isArchive
    ));
}
/*
このブロックは、アーカイブページでご利用になれます。

現在表示している年月の、翌月へまたは前月のリンクを出力します。

また、月別アーカイブページと同一テンプレートですが、カテゴリーアーカイブページでは、このブロックは表示されません。

1.8.6～
<div b_block:id="has_prev_or_next_month">
    <div b_block:id="has_prev_month">
        <a b_block:id="prev_month">前へ</a b_block:id="prev_month">
    </div>
    <div b_block:id="has_next_month">
        <a b_block:id="next_month">前へ</a b_block:id="next_month">
    </div>
</div>

 */
function soy_cms_blog_output_prev_next_month($page){

    $isMonthArchive = true;
    $hasNext = false;
    $hasPrev = false;
    $url = $page->getMonthPageURL(true);
    $nextMonthDate = $prevMonthDate = "";

    //アーカイブページ(年月日)ではない
    if($page->mode != CMSBlogPage::MODE_MONTH_ARCHIVE){
        $isMonthArchive = false;
    }

    //年月ではない
    if(is_null($page->year) || is_null($page->month) || !is_null($page->day)){
        $isMonthArchive = false;
    }

    if($isMonthArchive){
        $logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

        $thisMonth = date('Y-m-01', @mktime(0,0,0,$page->month,1,$page->year));
        $nextMonthDate = date('Y/m', strtotime($thisMonth . '+1 month'));
        $prevMonthDate = date('Y/m', strtotime($thisMonth . '-1 month'));

        //翌月の記事の存在確認
        try{
            $month_list = $logic->getCountMonth(array($page->page->getBlogLabelId()));
        }catch(Exception $e){
            $month_list = array();
        }
        foreach($month_list as $time => $count){
            if($count && date('Y/m', $time) == $nextMonthDate){
                $hasNext = true;
                break;
            }
        }


        //前月の記事の存在確認
        try{
            $month_list = $logic->getCountMonth(array($page->page->getBlogLabelId()));
        }catch(Exception $e){
            $month_list = array();
        }
        foreach($month_list as $time => $count){
            if($count && date('Y/m', $time) == $prevMonthDate){
                $hasPrev = true;
                break;
            }
        }
    }

    //翌月のページへ
    $page->createAdd("next_month","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasNext,
        "link" => $url.$nextMonthDate
    ));
    //翌月のページがあれば表示
    $page->createAdd("has_next_month","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => $hasNext,
    ));

    //前月のページへ
    $page->createAdd("prev_month","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev,
        "link" => $url.$prevMonthDate
    ));
    //前月のページがあれば表示
    $page->createAdd("has_prev_month","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev,
    ));

    //翌月または前月のページがあれば表示
    $page->createAdd("has_prev_or_next_month","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev || $hasNext,
    ));

}
