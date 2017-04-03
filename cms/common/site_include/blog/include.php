<?php
/*
このブロックは全てのブログページでご利用になれます。

このブロックは、当該ブログのトップページへのリンクを出力します。

このブロックは必ずAタグに使用してください。
<a b_block:id="top_link">ブログのトップへ</a b_block:id="top_link">
*/
function soy_cms_blog_output_top_link($page){
	$page->createAdd("top_link","HTMLLink",array(
		"soy2prefix" => "b_block",
		"link" => $page->getTopPageURL(true)
	));
}

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

	if(!class_exists("BlogPage_CategoryList")){
		/**
		 * カテゴリーを表示
		 */
		class BlogPage_CategoryList extends HTMLList{

			var $categoryUrl;
			private $entryCount = 0;

			function setCategoryUrl($categoryUrl){
				$this->categoryUrl = $categoryUrl;
			}

			protected function populateItem($entry){

				$this->createAdd("category_link","HTMLLink",array(
					"link"=>$this->categoryUrl . rawurlencode($entry->getAlias()),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("category_name","CMSLabel",array(
					"text"=>$entry->getBranchName(),
					"soy2prefix"=>"cms"
				));
				
				$this->createAdd("category_alias","CMSLabel",array(
					"text"=>$entry->getAlias(),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("entry_count","CMSLabel",array(
					"text"=>$this->entryCount[$entry->getid()],
					"soy2prefix"=>"cms"
				));

				$this->createAdd("label_id","CMSLabel",array(
					"text"=>$entry->getid(),
					"soy2prefix"=>"cms"
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
			}

			function getEntryCount() {
				return $this->entryCount;
			}
			function setEntryCount($entryCount) {
				$this->entryCount = $entryCount;
			}
		}
	}

	//ラベル一覧を取得：ラベルの表示順を反映する
	$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
	$labels = $labelDao->get();//表示順に並んでいる

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

	$page->createAdd("category","BlogPage_CategoryList",array(
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
function soy_cms_blog_output_archive_link($page){
	$labels = array($page->page->getBlogLabelId());


	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
	//取得までできているので、整形や表示を設定する
	$month_list = $logic->getCountMonth($labels);

	foreach($month_list as $key => $month){
		if($month == 0){
			unset($month_list[$key]);
		}
	}

	if(!class_exists("BlogPage_MonthArciveList")){

		/**
		 * 月別アーカイブを表示
		 */
		class BlogPage_MonthArciveList extends HTMLList{

			var $monthPageUri;
			var $format;

			function setMonthPageUri($uri){
				$this->monthPageUri = $uri;
			}

			function setFormat($format){
				$this->format = $format;
			}

			protected function populateItem($count,$key){

				$this->createAdd("archive_link","HTMLLink",array(
					"link" => $this->monthPageUri . date('Y/m',$key),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("archive_month","DateLabel",array(
					"text"=>$key,
					"soy2prefix"=>"cms",
					"defaultFormat"=>"Y年n月"
				));
				$this->createAdd("entry_count","CMSLabel",array(
					"text"=>$count,
					"soy2prefix"=>"cms"
				));

			}

		}
	}

	$page->createAdd("archive","BlogPage_MonthArciveList",array(
		"list" => $month_list,
		"monthPageUri" => $page->getMonthPageURL(true),
		"soy2prefix" => "b_block"
	));
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
	$labels = array($page->page->getBlogLabelId());


	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
	//取得までできているので、整形や表示を設定する
	$year_list = $logic->getCountYear($labels);

	foreach($year_list as $key => $count){
		if($count == 0){
			unset($year_list[$key]);
		}
	}

	if(!class_exists("BlogPage_YearArciveList")){

		/**
		 * 月別アーカイブを表示
		 */
		class BlogPage_YearArciveList extends HTMLList{

			var $yearPageUri;
			var $format;

			function setYearPageUri($uri){
				$this->yearPageUri = $uri;
			}

			function setFormat($format){
				$this->format = $format;
			}

			protected function populateItem($count,$key){

				$this->createAdd("archive_link","HTMLLink",array(
					"link" => $this->yearPageUri . date('Y',$key),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("archive_year","DateLabel",array(
					"text"=>$key,
					"soy2prefix"=>"cms",
					"defaultFormat"=>"Y年"
				));
				$this->createAdd("entry_count","CMSLabel",array(
					"text"=>$count,
					"soy2prefix"=>"cms"
				));

			}

		}
	}

	$page->createAdd("archive_by_year","BlogPage_YearArciveList",array(
		"list" => $year_list,
		"yearPageUri" => $page->getMonthPageURL(true),
		"soy2prefix" => "b_block"
	));
}

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
function soy_cms_blog_output_recent_entry_list($page,$entries){

	if(!class_exists("Blog_RecentEntryList")){
		class Blog_RecentEntryList extends HTMLList{

			var $entryPageUri;

			function setEntryPageUri($uri){
				$this->entryPageUri = $uri;
			}

			function populateItem($entry){

				$link = $this->entryPageUri . rawurlencode($entry->getAlias());

				$this->createAdd("entry_id","CMSLabel",array(
					"text" => $entry->getId(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("title","CMSLabel",array(
					"text" => $entry->getTitle(),
					"soy2prefix" => "cms"
				));
				
				//同じ意味だけど、他のブロックと合わせてtitle_plainを追加しておく
				$this->createAdd("title_plain","CMSLabel",array(
					"text" => $entry->getTitle(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("entry_link","HTMLLink",array(
					"link" => $link,
					"soy2prefix" => "cms"
				));

				$this->createAdd("create_date","DateLabel",array(
					"text"=>$entry->getCdate(),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("create_time","DateLabel",array(
					"text"=>$entry->getCdate(),
					"soy2prefix"=>"cms",
					"defaultFormat"=>"H:i"
				));
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

	$page->createAdd("recent_entry_list","Blog_RecentEntryList",array(
		"list" => $entries,
		"entryPageUri"=> $page->getEntryPageURL(true),
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

	if(!class_exists("Blog_RecentCommentList")){
		class Blog_RecentCommentList extends HTMLList{

			var $entryPageUri;

			function setEntryPageUri($uri){
				$this->entryPageUri = $uri;
			}

			function populateItem($comment){

				$this->createAdd("entry_title","CMSLabel",array(
					"text" => $comment->getEntryTitle(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("title","CMSLabel",array(
					"text" => $comment->getTitle(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("author","CMSLabel",array(
					"text" => $comment->getAuthor(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("submit_date","DateLabel",array(
					"text" => $comment->getSubmitDate(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("submit_time","DateLabel",array(
					"text"=>$comment->getSubmitDate(),
					"soy2prefix"=>"cms",
					"defaultFormat"=>"H:i"
				));

				$this->createAdd("entry_link","HTMLLink",array(
					"link" => $this->entryPageUri . rawurlencode($comment->getAlias()),
					"soy2prefix" => "cms"
				));


				/* 以下1.2.8～ */
				$comment_body = str_replace("\n","@@@@__BR__MARKER__@@@@",$comment->getBody());
				$comment_body = htmlspecialchars($comment_body, ENT_QUOTES, "UTF-8");
				$comment_body = str_replace("@@@@__BR__MARKER__@@@@","<br>",$comment_body);

				$this->createAdd("body","CMSLabel",array(
					"html" => $comment_body,
					"soy2prefix" => "cms"
				));

				$this->createAdd("url","HTMLLink",array(
					"link" => $comment->getUrl(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("mail_address","HTMLLink",array(
					"link" => "mailto:".$comment->getMailAddress(),
					"soy2prefix" => "cms"
				));

			}
		}
	}

	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
	$comments = $logic->getRecentComments(array($page->page->getBlogLabelId()));
	try{
		$page->createAdd("recent_comment_list","Blog_RecentCommentList",array(
			"list" => $comments,
			"entryPageUri" => $page->getEntryPageURL(true),
			"soy2prefix" => "b_block"
		));
	}catch(Exception $e){

	}
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

	if(!class_exists("Blog_RecentTrackBackList")){
		class Blog_RecentTrackBackList extends HTMLList{

			var $entryPageUri;

			function setEntryPageUri($uri){
				$this->entryPageUri = $uri;
			}

			function populateItem($trackback){
				$link = $this->entryPageUri . rawurlencode($trackback->getAlias());

				$this->createAdd("title","CMSLabel",array(
					"text"=>$trackback->getTitle(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("url","HTMLLink",array(
					"link"=>$trackback->getUrl(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("blog_name","CMSLabel",array(
					"text"=>$trackback->getBlogName(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("excerpt","CMSLabel",array(
					"text"=>$trackback->getExcerpt(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("submit_date","DateLabel",array(
					"text"=>$trackback->getSubmitdate(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("submit_time","DateLabel",array(
					"text"=>$trackback->getSubmitdate(),
					"soy2prefix"=>"cms",
					"defaultFormat"=>"H:i"
				));
				$this->createAdd("entry_link","HTMLLink",array(
					"link"=>$link,
					"soy2prefix"=>"cms"
				));

				$this->createAdd("entry_title","CMSLabel",array(
					"text"=>$trackback->getEntryTitle(),
					"soy2prefix"=>"cms"
				));

			}
		}
	}

	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic");
	$trackbacks = $logic->getRecentTrackbacks(array($page->page->getBlogLabelId()));
	try{
		$page->createAdd("recent_trackback_list","Blog_RecentTrackBackList",array(
			"list" => $trackbacks,
			"entryPageUri" => $page->getEntryPageURL(true),
			"soy2prefix" => "b_block"
		));
	}catch(Exception $e){

	}
}

/**
 * RSS2.0を出力
 */
function soy_cms_blog_output_rss($page, $entries, $title = null, $charset = "UTF-8"){
	function soy_cms_blog_output_rss_h($string){
		return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	}
	function soy_cms_blog_output_rss_cdata($html){
		//タグを除去してエンティティを戻す
		$text = SOY2HTML::ToText($html);
		// ]]> があったらそこで分割する
		$cdata = "<![CDATA[" . str_replace("]]>", "]]]]><![CDATA[>", $text) ."]]>";
		return $cdata;
	}

	$entry = @$entries[0];
	$update = ($entry) ? $entry->getUdate() : $page->page->getUdate();
	$entryPageUrl = $page->getEntryPageURL(true);

	if(is_null($title)) $title = $page->page->getTitle();

	$xml = array();

	$xml[] = '<?xml version="1.0" encoding="'.$charset.'" ?>';
	$xml[] = '<rss version="2.0">';
	$xml[] = '<channel>';
	$xml[] = '<title>'.soy_cms_blog_output_rss_h($title).'</title>';
	$xml[] = '<link>'.soy_cms_blog_output_rss_h($page->getTopPageURL(true)).'</link>';
	$xml[] = '<description>'.soy_cms_blog_output_rss_h($page->page->getDescription()).'</description>';
	$xml[] = '<pubDate>'.soy_cms_blog_output_rss_h(date('r',$update)).'</pubDate>';
	$xml[] = '<generator>'.'SOY CMS '.SOYCMS_VERSION.'</generator>';
	$xml[] = '<docs>http://blogs.law.harvard.edu/tech/rss</docs>';
	$xml[] = '<language>'.( defined("SOYCMS_LANGUAGE") ? SOYCMS_LANGUAGE : "ja" ).'</language>';

	foreach($entries as $entry){

		$buildDate = max($entry->getCdate(),$entry->getUdate());
		$update = max($buildDate, $update);

		$xml[] = '<item>';
		$xml[] = '<title>'.soy_cms_blog_output_rss_h($entry->getTitle()).'</title>';
		$xml[] = '<link>'.soy_cms_blog_output_rss_h($entryPageUrl . rawurlencode($entry->getAlias())) .'</link>';
		$xml[] = '<guid isPermaLink="false">'.soy_cms_blog_output_rss_h($entryPageUrl . $entry->getId()) .'</guid>';
		$xml[] = '<pubDate>'.soy_cms_blog_output_rss_h(date('r',$entry->getCdate())).'</pubDate>';
		//$xml[] = '<lastBuildDate>'.soy_cms_blog_output_rss_h(date('r',$buildDate)).'</lastBuildDate>';
		$xml[] = '<description>'. soy_cms_blog_output_rss_cdata( ( strlen($entry->getDescription()) >0 ) ? $entry->getDescription() : $entry->getContent() ) . '</description>';
		$xml[] = '</item>';

	}

	$xml[] = '<lastBuildDate>'.soy_cms_blog_output_rss_h(date('r',$update)).'</lastBuildDate>';

	$xml[] = '</channel>';
	$xml[] = '</rss>';

	echo implode("\n",$xml);

}

/*
 * ATOM出力
 */
function soy_cms_blog_output_atom($page, $entries, $title = null, $charset = "UTF-8"){
	function soy_cms_blog_output_atom_h($string){
		return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	}
	function soy_cms_blog_output_atom_cdata($string){
		// ]]> があったらそこで分割する
		$cdata = str_replace("]]>", "]]]]><![CDATA[>", $string);
		$cdata = "<![CDATA[" . $cdata ."]]>";
		return $cdata;
	}

	$entry = @$entries[0];
	$update = ($entry) ? $entry->getUdate() : $page->page->getUdate();
	$entryPageUrl = $page->getEntryPageURL(true);
	if(is_null($title)) $title = $page->page->getTitle();

	$xml = array();

	$xml[] = '<?xml version="1.0" encoding="'.$charset.'" ?>';
	$xml[] = '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">';
	$xml[] = '<title>'.soy_cms_blog_output_atom_h($title).'</title>';
	$xml[] = '<subtitle type="html">'.soy_cms_blog_output_atom_h($page->page->getDescription()).'</subtitle>';
	$xml[] = '<link rel="alternate" href="'.soy_cms_blog_output_atom_h($page->getTopPageURL(true)).'" />';
	$xml[] = '<link rel="self" type="application/atom+xml" href="'.soy_cms_blog_output_atom_h($page->getRssPageURL(true)."?feed=atom").'" />';
	$xml[] = '<author><name>'.soy_cms_blog_output_atom_h($page->page->getAuthor()).'</name></author>';
	$xml[] = '<id>'.soy_cms_blog_output_atom_h($page->getTopPageURL(true)).'</id>';

	foreach($entries as $entry){
		$buildDate = max($entry->getCdate(),$entry->getUdate());
		$update = max($buildDate, $update);
	}
	$xml[] = '<updated>'.soy_cms_blog_output_atom_h(date('c',$update)).'</updated>';

	foreach($entries as $entry){

		$buildDate = max($entry->getCdate(),$entry->getUdate());

		$xml[] = '<entry>';
		$xml[] = '<title>'.soy_cms_blog_output_atom_h($entry->getTitle()).'</title>';
		$xml[] = '<link rel="alternate" href="'. soy_cms_blog_output_atom_h($entryPageUrl . rawurlencode($entry->getAlias())) .'" type="application/xhtml+xml"/>';
		$xml[] = '<published>'.soy_cms_blog_output_atom_h(date('c',$entry->getCdate())).'</published>';
		$xml[] = '<updated>'.soy_cms_blog_output_atom_h(date('c',$buildDate)).'</updated>';
		$xml[] = '<id>'.soy_cms_blog_output_atom_h($entryPageUrl.$entry->getId()).'</id>';
		if(strlen($entry->getDescription()) >0){
			$xml[] = '<summary>'.soy_cms_blog_output_atom_h($entry->getDescription()).'</summary>';
		}
		$xml[] = '<content type="html">' . soy_cms_blog_output_atom_cdata($entry->getContent()) . '</content>';
		$xml[] = '</entry>';
	}

	$xml[] = '</feed>';

	echo implode("\n",$xml);

}

/*
 * フィードのメタ情報を出力
 * <!-- b_block:id="meta_feed_link" --><!--/b_block:id="meta_feed_link" -->
 */
function soy_cms_blog_output_meta_feed_info($page){

	$url = $page->getRssPageURL();

	$hUrl = htmlspecialchars($url, ENT_QUOTES, "UTF-8");
	$hTitle = htmlspecialchars($page->page->getTitle(), ENT_QUOTES, "UTF-8");

	$text = '<link rel="alternate" type="application/rss+xml" title="'.$hTitle.'" href="'.$hUrl.'?feed=rss" />'."\n";
	$text .= '<link rel="alternate" type="application/atom+xml" title="'.$hTitle.'" href="'.$hUrl.'?feed=atom" />';

	$page->createAdd("meta_feed_link","HTMLLabel",array(
		"html" => $text,
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));
}

/*
 *  feedのリンクを表示
 *  <a b_block:id="rss_link">RSS</a>
 *  <a b_block:id="atom_link">ATOM</a>
 */
function soy_cms_blog_output_feed_link($page){

	$url = $page->getRssPageURL(true);

	$page->createAdd("rss_link","HTMLLink",array(
		"link" => $url ."?feed=rss",
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));

	$page->createAdd("atom_link","HTMLLink",array(
		"link" => $url ."?feed=atom",
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));
}
?>