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

    if(!class_exists("BlogPage_EntryList_CategoryList")){
        class BlogPage_EntryList_CategoryList extends HTMLList{

			var $categoryPageUrl;
			var $entryCount;

			function setCategoryPageUrl($categoryPageUrl){
                $this->categoryPageUrl = $categoryPageUrl;
            }

			function setEntryCount($entryCount){
				$this->entryCount = $entryCount;
			}

            protected function populateItem($entry){
                $this->createAdd("category_link","HTMLLink",array(
                    "link"=>$this->categoryPageUrl . rawurlencode($entry->getAlias()),
                    "soy2prefix"=>"cms"
                ));
                $this->createAdd("category_name","CMSLabel",array(
                    "text"=>$entry->getBranchName(),
                    "soy2prefix"=>"cms"
                ));
                $this->createAdd("label_id","CMSLabel",array(
                    "text"=>$entry->getId(),
                    "soy2prefix"=>"cms"
                ));

                $this->createAdd("category_alias", "CMSLabel", array(
                    "text" => $entry->getAlias(),
                    "soy2prefix" => "cms"
                ));

                $this->createAdd("category_description", "CMSLabel", array(
                    "text" => $entry->getDescription(),
                    "soy2prefix" => "cms"
                ));

                $arg = substr(rtrim($_SERVER["REQUEST_URI"], "/"), strrpos(rtrim($_SERVER["REQUEST_URI"], "/"), "/") + 1);
                $alias = rawurlencode($entry->getAlias());
                $this->createAdd("is_current_category", "HTMLModel", array(
                    "visible" => ($arg === $alias),
                    "soy2prefix" => "cms"
                ));
                $this->createAdd("no_current_category", "HTMLModel", array(
                    "visible" => ($arg !== $alias),
                    "soy2prefix" => "cms"
                ));

                $this->addLabel("color", array(
                    "text" => sprintf("%06X",$entry->getColor()),
                    "soy2prefix" => "cms"
                ));

                $this->addLabel("background_color", array(
                    "text" => sprintf("%06X",$entry->getBackGroundColor()),
                    "soy2prefix" => "cms"
                ));

				$this->addLabel("entry_count", array(
					"text" => (isset($this->entryCount[$entry->getId()])) ? $this->entryCount[$entry->getId()] : 0,
					"soy2prefix" => "cms"
				));
            }
        }
    }

    if(!class_exists("BlogPage_EntryList")){

        /**
         * 記事を表示
         */
        class BlogPage_EntryList extends HTMLList{

            var $entryCommentDAO;
            var $entryTrackbackDAO;

            var $entryPageUrl;
            var $categoryPageUrl;

            var $blogLabelId;

            var $categoryLabelList;
			var $entryCount;

            var $_commentDAO;
            var $_trackbackDAO;

            function getEntryCommentDAO(){
                if(!$this->_commentDAO){
                    $this->_commentDAO = SOY2DAOFactory::create("cms.EntryCommentDAO");
                }

                return $this->_commentDAO;
            }

            function getEntryTrackbackDAO(){
                if(!$this->_trackbackDAO){
                    $this->_trackbackDAO = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
                }

                return $this->_trackbackDAO;
            }

            function setEntryPageUrl($entryPageUrl){
                $this->entryPageUrl = $entryPageUrl;
            }
            function setCategoryPageUrl($categoryPageUrl){
                $this->categoryPageUrl = $categoryPageUrl;
            }
            function setBlogLabelId($blogLabelId){
                $this->blogLabelId = $blogLabelId;
            }
            function setCategoryLabelList($categoryLabelList){
                $this->categoryLabelList = $categoryLabelList;
            }
			function setEntryCount($entryCount){
				$this->entryCount = $entryCount;
			}

            protected function populateItem($entry){

                $this->createAdd("entry_id","CMSLabel",array(
                    "text"=>$entry->getId(),
                    "soy2prefix"=>"cms"
                ));

                $link = $this->entryPageUrl . rawurlencode($entry->getAlias()) ;

                $this->createAdd("title","CMSLabel",array(
                    "html"=> "<a href=\"$link\">".htmlspecialchars($entry->getTitle(), ENT_QUOTES, "UTF-8")."</a>",
                    "soy2prefix"=>"cms"
                ));

                $this->createAdd("title_plain","CMSLabel",array(
                    "text"=> $entry->getTitle(),
                    "soy2prefix"=>"cms"
                ));

                $this->createAdd("content","CMSLabel",array(
                    "html"=>$entry->getContent(),
                    "soy2prefix"=>"cms"
                ));
                $this->createAdd("more","CMSLabel",array(
                    "html"=>$entry->getMore(),
                    "soy2prefix"=>"cms"
                ));
                $this->createAdd("create_date","DateLabel",array(
                    "text"=>$entry->getCdate(),
                    "soy2prefix"=>"cms",
                ));

                $this->createAdd("create_time","DateLabel",array(
                    "text"=>$entry->getCdate(),
                    "soy2prefix"=>"cms",
                    "defaultFormat"=>"H:i"
                ));

                $this->createAdd("entry_link","HTMLLink",array(
                    "soy2prefix"=>"cms",
                    "link" => $link
                ));

                $this->createAdd("more_link","HTMLLink",array(
                    "soy2prefix"=>"cms",
                    "link" => $link ."#more",
                    "visible"=>(strlen($entry->getMore()) != 0)
                ));

                $this->createAdd("more_link_no_anchor", "HTMLLink", array(
                    "soy2prefix"=>"cms",
                    "link" => $link,
                    "visible"=>(strlen($entry->getMore()) != 0)
                ));

                $this->createAdd("trackback_link","HTMLLink",array(
                    "soy2prefix"=>"cms",
                    "link" => $link ."#trackback_list"
                ));

                $this->createAdd("trackback_count","CMSLabel",array(
                    "soy2prefix"=>"cms",
                    "text" => $entry->getTrackbackCount()
                ));

                $this->createAdd("comment_link","HTMLLink",array(
                    "soy2prefix"=>"cms",
                    "link" => $link ."#comment_list"
                ));

                $this->createAdd("comment_count","CMSLabel",array(
                    "soy2prefix"=>"cms",
                    "text" => $entry->getCommentCount()
                ));

                $this->createAdd("category_list","BlogPage_EntryList_CategoryList",array(
                    "list" => $entry->getLabels(),
                    "categoryPageUrl" => $this->categoryPageUrl,
					"entryCount" => $this->entryCount,
                    "soy2prefix" => "cms"
                ));

                CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entry->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entry));

                //Messageの追加
                $this->addMessageProperty("entry_id",'<?php echo $'.$this->_soy2_id.'["entry_id"]; ?>');
                $this->addMessageProperty("title",'<?php echo $'.$this->_soy2_id.'["title_plain"]; ?>');
                $this->addMessageProperty("content",'<?php echo $'.$this->_soy2_id.'["content"]; ?>');
                $this->addMessageProperty("more",'<?php echo $'.$this->_soy2_id.'["more"]; ?>');
                $this->addMessageProperty("create_date",'<?php echo $'.$this->_soy2_id.'["create_date"]; ?>');
                $this->addMessageProperty("entry_link",'<?php echo $'.$this->_soy2_id.'["entry_link_attribute"]["href"]; ?>');
                $this->addMessageProperty("more_link",'<?php echo $'.$this->_soy2_id.'["more_link_attribute"]["href"]; ?>');
                $this->addMessageProperty("trackback_link",'<?php echo $'.$this->_soy2_id.'["trackback_link_attribute"]["href"]; ?>');
                $this->addMessageProperty("comment_link",'<?php echo $'.$this->_soy2_id.'["comment_link_attribute"]["href"]; ?>');
            }

            function getStartTag(){

                if(defined("CMS_PREVIEW_MODE")){
                    return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->_soy2_id.'["entry_id"]; ?>','<?php echo strip_tags($'.$this->_soy2_id.'["title"]); ?>');
                }else{
                    return parent::getStartTag();
                }
            }

        }
    }

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

    $page->createAdd("entry_list","BlogPage_EntryList",array(
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

    if(!class_exists("BlogPage_EntryListPager")){
        /**
         * ページャーを表示
         */
        class BlogPage_EntryListPager extends HTMLList{
            //今のページ番号
            var $current;
            //最大ページ数
            var $last;
            //ベースURL=最初のページのURL
            var $url;
            function setCurrent($current){
                $this->current = $current;
            }
            function setLast($last){
                $this->last = $last;
            }
            function setUrl($url){
                $this->url = $url;
            }

            /**
             * cms:pager_numのためにオーバーライド
             */
            function execute(){
                //ページャーの表示件数（デフォルトは10）
                $pager_display_number = $this->getAttribute("cms:pager_num");
                if(strlen($pager_display_number) ==0) $pager_display_number = 10;

                $display_start = max(1, min($this->current - floor($pager_display_number/2), $this->last - $pager_display_number+1));
                $display_end   = min($this->last, max($pager_display_number, $this->current + floor(($pager_display_number-1)/2)));

                $this->list = array();
                for($page_num=$display_start;$page_num<=$display_end;$page_num++){
                    $url = $this->url;

                    //2ページ以降は/page-2を付ける
                    if($page_num > 1){
                        $url .= (strlen($url) ==0 OR $url[strlen($url)-1] != "/") ? "/" : "" ;
                        $url .= "page-" . ($page_num -1);
                    }

                    $this->list[] = array(
                        "display_number" => $page_num,
                        "url" => $url
                    );
                }

                parent::execute();
            }

            protected function populateItem($pager_list){

                $html = "<a href=\"".htmlspecialchars($pager_list["url"], ENT_QUOTES, "UTF-8")."\"";

                $class = array();
                if($pager_list["display_number"] == $this->current) $class[] = "current_page_number";
                if($pager_list["display_number"] == 1) $class[] = "first_page_number";// 1.3.4-
                if($pager_list["display_number"] == $this->last) $class[] = "last_page_number";// 1.3.4-
                if(count($class)) $html .= " class=\"".implode(" ",$class)."\"";

                $html .= ">";
                $html .= htmlspecialchars($pager_list["display_number"], ENT_QUOTES, "UTF-8");
                $html .= "</a>";

                $this->createAdd("pager_item_link", "HTMLLink", array(
                    "link" => htmlspecialchars($pager_list["url"], ENT_QUOTES, "UTF-8"),
                    "text" => htmlspecialchars($pager_list["display_number"], ENT_QUOTES, "UTF-8"),
                    "soy2prefix" => "cms"
                ));
                $this->createAdd("pager_item","HTMLLabel",array(
                    "html" => $html,
                    "soy2prefix" => "cms"
                ));

                //1.2.8～
                $this->createAdd("is_first","HTMLModel",array(
                    "visible" => ($pager_list["display_number"] == 1),
                    "soy2prefix" => "cms"
                ));
                $this->createAdd("is_last","HTMLModel",array(
                    "visible" => ($pager_list["display_number"] == $this->last),
                    "soy2prefix" => "cms"
                ));
                $this->createAdd("is_current","HTMLModel",array(
                    "visible" => ($pager_list["display_number"] == $this->current),
                    "soy2prefix" => "cms"
                ));
            }

        }
    }

    //今のページ番号
    $current = max(1, $offset + 1);
    //最大ページ数=ページの数
    $last_page_number = $limit ? max(1, ceil($total / $limit)) : 1;

    $page->createAdd("pager","BlogPage_EntryListPager",array(
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

    if(!class_exists("Blog_CurrentCategoryOrArchive")){
        class Blog_CurrentCategoryOrArchive extends SOYBodyComponentBase{
            function setPage($page){
                $alias = null;
                $link = null;

                switch($page->mode){
                    case CMSBlogPage::MODE_CATEGORY_ARCHIVE :
                        $this->createAdd("archive_name","CMSLabel",array(
                            "text"=> ( ($page->label) ? $page->label->getBranchName() : "" ),
                            "soy2prefix"=>"cms"
                        ));
                        if($page->label){
                            $link = $page->getCategoryPageURL(true) . rawurlencode($page->label->getAlias());
                            $alias = $page->label->getAlias();
                        }
                        break;

                    case CMSBlogPage::MODE_MONTH_ARCHIVE :
                    default:
                        if(!$page->year){
                            $date = time();
                            $link = date("Y/m", time());
                        }elseif(!$page->month){
                            $date = @mktime(0,0,0,1,1,$page->year);
                            $link = date("Y",$date);
                        }elseif(!$page->day){
                            $date = @mktime(0,0,0,$page->month,1,$page->year);
                            $link = date("Y/m",$date);
                        }else{
                            $date = @mktime(0,0,0,$page->month,$page->day,$page->year);
                            $link = date("Y/m/d",$date);
                        }
                        $this->createAdd("archive_name","DateLabel",array(
                            "year"  => $page->year,
                            "month" => $page->month,
                            "day"   => $page->day,
                            "soy2prefix"=>"cms",
                            "defaultFormat"=>"Y年n月"
                        ));
                        $link = $page->getMonthPageURL(true) . $link;
                        break;
                }
                $this->createAdd("archive_link","HTMLLink",array(
                    "link"=> $link,
                    "soy2prefix"=>"cms"
                ));

                $this->createAdd("category_alias","CMSLabel",array(
                    "text"=>$alias,
                    "soy2prefix"=>"cms"
                ));
            }
        }
    }
    $page->createAdd("current_category_or_archive","Blog_CurrentCategoryOrArchive",array(
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

    if(!class_exists("Blog_CurrentCategory")){
        class Blog_CurrentCategory extends SOYBodyComponentBase{
            function setPage($page){
                $this->createAdd("category_name","CMSLabel",array(
                    "text"=>($page->label)? $page->label->getBranchName() : "",
                    "soy2prefix"=>"cms"
                ));

                $this->createAdd("category_link","HTMLLink",array(
                    "link"=>($page->label)? $page->getCategoryPageURL(true) . rawurlencode($page->label->getAlias()) : "",
                    "soy2prefix"=>"cms"
                ));
            }
        }
    }

    $page->createAdd("current_category","Blog_CurrentCategory",array(
        "soy2prefix"=>"b_block",
        "page"=>$page,
        "visible"=>($page->mode==CMSBlogPage::MODE_CATEGORY_ARCHIVE)
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

    if(!class_exists("Blog_CurrentArchive")){
        class Blog_CurrentArchive extends SOYBodyComponentBase{
            function setPage($page){

                if(!$page->year){
                    $date = time();
                    $link = date("Y/m", $date);
                }elseif(!$page->month){
                    $date = @mktime(0,0,0,1,1,$page->year);
                    $link = date("Y",$date);
                }elseif(!$page->day){
                    $date = @mktime(0,0,0,$page->month,1,$page->year);
                    $link = date("Y/m",$date);
                }else{
                    $date = @mktime(0,0,0,$page->month,$page->day,$page->year);
                    $link = date("Y/m/d",$date);
                }

                $this->createAdd("archive_month","DateLabel",array(
                    "year"  => $page->year,
                    "month" => $page->month,
                    "day"   => $page->day,
                    "soy2prefix"=>"cms",
                    "defaultFormat"=>"%Y:Y年%%M:n月%%D:j日%"
                ));

                $this->createAdd("archive_date","DateLabel",array(
                    "year"  => $page->year,
                    "month" => $page->month,
                    "day"   => $page->day,
                    "soy2prefix"=>"cms",
                    "defaultFormat"=> "%Y:Y年%%M:n月%%D:j日%"
                ));

                $this->createAdd("archive_link","HTMLLink",array(
                    "link"=> $page->getMonthPageURL(true) . $link,
                    "soy2prefix"=>"cms"
                ));
            }
        }
    }

    $page->createAdd("current_archive","Blog_CurrentArchive",array(
        "soy2prefix"=>"b_block",
        "page"=>$page,
        "visible"=>(($page->mode==CMSBlogPage::MODE_MONTH_ARCHIVE)&&!$page->day)
    ));

    $page->createAdd("current_archive_date","Blog_CurrentArchive",array(
        "soy2prefix"=>"b_block",
        "page"=>$page,
        "visible"=>(($page->mode==CMSBlogPage::MODE_MONTH_ARCHIVE)&&$page->day)
    ));

    $page->createAdd("current_archive_ymd","Blog_CurrentArchive",array(
        "soy2prefix"=>"b_block",
        "page"=>$page,
        "visible"=>($page->mode==CMSBlogPage::MODE_MONTH_ARCHIVE)
    ));

    $page->addModel("is_archive_page", array(
        "soy2prefix"=>"b_block",
        "visible"=>($page->mode==CMSBlogPage::MODE_MONTH_ARCHIVE)
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
