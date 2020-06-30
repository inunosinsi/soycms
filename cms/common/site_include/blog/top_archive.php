<?php
/**
 * 現在のURLを返します。
 * ページャー部分は除きます。
 */
function soy_cms_blog_get_current_url($page, $offset = 0){

        $page_param = ( $offset > 0 ) ? "page-" . $offset : "" ;

        switch($page->mode){
            case CMSBlogPage::MODE_MONTH_ARCHIVE:
                $url = $page->getMonthPageURL(true);
                if(strlen($page->year)){
                    $url .=  $page->year;//末尾にスラッシュは付けない
                    if(strlen($page->month)){
                        $url .= "/" . $page->month;//末尾にスラッシュは付けない
                    }
                }
                break;
            case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
                $url = $page->getCategoryPageURL(true) . rawurlencode($page->label->getAlias());//末尾にスラッシュは付けない
                break;
            case CMSBlogPage::MODE_TOP:
            default:
                $url = $page->getTopPageURL(true);//トップページのURIが空の時は末尾にスラッシュが付く
                break;
        }

        if(strlen($page_param)){
            $url .= (strlen($url) ==0 OR $url[strlen($url)-1] != "/") ? "/" : "" ;
            $url .= $page_param;
        }

        return $url;
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。

このブロックは、繰り返しブロックであり、該当する記事の個数だけブロックの内容が繰り返し出力されます。

    トップページの場合は、全ての記事を作成日の新しい順に、

    アーカイブページの場合は、当該カテゴリーまたは年月の記事を作成日の新しい順に、

それぞれ出力します。

1.2.4～：category_listを追加

<!-- b_block:id="entry_list" -->
<div>
    <h2 cms:id="title">ここにはタイトルが入ります</h2>
    <p cms:id="content">ここには本文が入ります</p>
         <a cms:id="more_link">続きを表示</a>
        |<a cms:id="entry_link">この記事を読む</a>
        |<a cms:id="comment_link">コメント(<!-- cms:id="comment_count" -->0<!-- /cms:id="comment_count" -->)</a>
        |<a cms:id="trackback_link">トラックバック(<!-- cms:id="trackback_count" -->0<!-- /cms:id="trackback_count" -->)</a>

    <p>
        <!-- cms:id="category_list" -->
            <a cms:id="category_link"><!-- cms:id="category_name" --><!-- /cms:id="category_name" --></a>
        <!-- /cms:id="category_list" -->
    </p>
</div>
<!-- /b_block:id="entry_list" -->
*/
function soy_cms_blog_output_entry_list($page,$entries){

    if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");
    if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");

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

    $page->createAdd("entry_list","EntryListComponent",array(
        "list" => $entries,
        "entryPageUrl" => $page->getEntryPageURL(true),
        "categoryPageUrl" => $page->getCategoryPageURL(true),
        "blogLabelId" => (int)$page->page->getBlogLabelId(),
        "categoryLabelList" => $page->page->getCategoryLabelList(),
		"entryCount" => $entryCount,
        "soy2prefix" => "b_block"
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。

このブロックはページャーを出力します。

1.2.7-
cms:id="pager_item" では表示中のページにcurrent_page_numberというクラスが自動的に設定されます。

<div class="pager">
    <a b_block:id="first_page">&lt;&lt;</a b_block:id="first_page">

    <!-- b_block:id="pager" cms:pager_num="10" -->
    <span class="pager_item" cms:id="pager_item">1</span>
    <!-- /b_block:id="pager" -->
    <!-- cms:ignore -->
        <span class="pager_item">2</span> <span class="pager_item">3</span> <span class="pager_item">4</span> <span class="pager_item">5</span> <span class="pager_item">6</span> <span class="pager_item">7</span> <span class="pager_item">8</span> <span class="pager_item">9</span> <span class="pager_item">10</span>
    <!-- /cms:ignore -->

    <a b_block:id="last_page">&gt;&gt;</a b_block:id="last_page">

    <!-- b_block:id="pages" /-->ページ中<!-- b_block:id="current_page" /-->ページ目
</div>

1.2.8-
    最初のページ、最後のページ、現在のページで何かを表示できる
    <!-- b_block:id="pager" cms:pager_num="10" -->
        <!-- cms:id="is_first" -->[[<!-- /cms:id="is_first" -->
        <!-- cms:id="is_current*" -->[<!-- /cms:id="is_current*" -->
        <span class="pager_item" cms:id="pager_item">1</span>
        <!-- cms:id="is_current*" -->]<!-- /cms:id="is_current*" -->
        <!-- cms:id="is_last" -->]]<!-- /cms:id="is_last" -->
    <!-- /b_block:id="pager" -->

1.3.4-
自動的に設定されるクラスにfirst_page_number, last_page_numberを追加
複数ページあるなしのb_block:idを追加

<!-- b_block:id="has_pager" -->
    ２ページ目以降があるときに表示されます。
<!-- /b_block:id="has_pager" -->

<!-- b_block:id="no_pager" -->
    １ページしかないとき（２ページ目以降がないとき）に表示されます。
<!-- /b_block:id="no_pager" -->

*/
function soy_cms_blog_output_entry_list_pager($page,$offset,$limit,$total){

    if(!class_exists("EntryListPagerComponent")) SOY2::import("site_include.blog.component.EntryListPagerComponent");

    //今のページ番号
    $current = max(1, $offset + 1);
    //最大ページ数=ページの数
    $last_page_number = $limit ? max(1, ceil($total / $limit)) : 1;

    $page->createAdd("pager","EntryListPagerComponent",array(
        "list" => array(),
        "current" => $current,
        "last"   => $last_page_number,
        "url"    => soy_cms_blog_get_current_url($page),
        "soy2prefix" => "b_block"
    ));

    $page->createAdd("has_pager","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => ($last_page_number >1)
    ));
    $page->createAdd("no_pager","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => ($last_page_number <2)
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
このブロックは最初のページ（1ページ目）へのリンクを出力します。
このブロックは必ずAタグに使用してください。

1.2.7～

<a b_block:id="first_page">&lt;&lt;</a b_block:id="first_page">
*/
function soy_cms_blog_output_first_page_link($page,$offset,$limit,$total){
    $url = soy_cms_blog_get_current_url($page);
    $page->createAdd("first_page","HTMLLink",array(
        "soy2prefix" => "b_block",
        "link" => $url,
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
このブロックは最後のページへのリンクを出力します。
このブロックは必ずAタグに使用してください。

1.2.7～

<a b_block:id="last_page">&gt;&gt;</a b_block:id="last_page">
*/
function soy_cms_blog_output_last_page_link($page,$offset,$limit,$total){
    $last_page = $limit ? max(1, ceil($total / $limit)) : 1;

    $url = soy_cms_blog_get_current_url($page, $last_page -1);

    $page->createAdd("last_page","HTMLLink",array(
        "soy2prefix" => "b_block",
        "link" => $url,
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
このブロックは現在のページ番号を出力します。

1.2.7～
<!-- b_block:id="pages" /-->ページ中<!-- b_block:id="current_page" /-->ページ目
*/
function soy_cms_blog_output_current_page($page,$offset){
    $page->createAdd("current_page","HTMLLabel",array(
        "soy2prefix" => "b_block",
        "text" => max(1, $offset + 1),
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
このブロックはページ数を出力します。

1.2.7～
<!-- b_block:id="pages" /-->ページ中<!-- b_block:id="current_page" /-->ページ目

1.3.4～
SOY Shopのtotal_pagesも使えるようにする
<!-- b_block:id="total_pages" /-->ページ中<!-- b_block:id="current_page" /-->ページ目
*/
function soy_cms_blog_output_pages($page, $limit,$total){
    $last_page = $limit ? max(1, ceil($total / $limit)) : 1;

    $page->createAdd("pages","HTMLLabel",array(
        "soy2prefix" => "b_block",
        "text" => $last_page,
    ));
    $page->createAdd("total_pages","HTMLLabel",array(
        "soy2prefix" => "b_block",
        "text" => $last_page,
    ));
}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
それぞれのページでの表示されている記事件数以上の記事があった場合、
このブロックの生成するリンクによって続きのページへと移動することができます。
続きがない場合は表示されません。
このブロックは必ずAタグに使用してください。

<a b_block:id="next_link">次へ</a b_block:id="next_link">

1.2.7-
<a b_block:id="next_page">次へ</a b_block:id="next_page">
1.3.4-
次のページがあるとき
<!-- b_block:id="has_next" -->続く<!-- /b_block:id="has_next" -->
次のページがないとき
<!-- b_block:id="no_next" -->終わり<!-- /b_block:id="no_next" -->
 */
function soy_cms_blog_output_next_link($page,$offset,$limit,$total){

    $url = soy_cms_blog_get_current_url($page, $offset +1);
    $hasNext = ($total > ($limit * ($offset + 1)));

    $page->createAdd("next_link","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasNext,
        "link" => $url,
    ));

    $page->createAdd("next_page","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasNext,
        "link" => $url,
    ));

    $page->createAdd("has_next","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => $hasNext
    ));

    $page->createAdd("no_next","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => !$hasNext
    ));

}

/*
このブロックはトップページ、アーカイブページでご利用になれます。
それぞれのページでの表示されている記事件数以上の記事があった場合、
より新しい記事がある場合には、このブロックの生成するリンクによって移動することが可能です。
現在表示している記事より新しい記事がない場合はこのブロックは表示されません。
このブロックは必ずAタグに使用してください。

<a b_block:id="prev_link">前へ</a b_block:id="prev_link">

1.2.7～
<a b_block:id="prev_page">次へ</a b_block:id="prev_page">
1.3.4-
前のページがないとき
<!-- b_block:id="no_prev" -->最初<!-- /b_block:id="no_prev" -->
1.6.1-
前のページがあるとき
<!-- b_block:id="has_prev" -->昔<!-- /b_block:id="has_prev" -->
 */
function soy_cms_blog_output_prev_link($page,$offset,$limit){

    $url = soy_cms_blog_get_current_url($page);
    if($offset > 1){
        $url .= (strlen($url) ==0 OR $url[strlen($url)-1] != "/") ? "/" : "" ;
        $url .= "page-" . ($offset -1);
    }
    $hasPrev = ($offset > 0);

    //前のページへ
    $page->createAdd("prev_link","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev,
        "link" => $url
    ));

    $page->createAdd("prev_page","HTMLLink",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev,
        "link" => $url
    ));

    $page->createAdd("has_prev","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => $hasPrev
    ));

    $page->createAdd("no_prev","HTMLModel",array(
        "soy2prefix" => "b_block",
        "visible" => !$hasPrev
    ));
}
