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

	if(!class_exists("BlogPage_Entry_CategoryList")){
		class BlogPage_Entry_CategoryList extends HTMLList{
			var $categoryPageUri;

			function setCategoryPageUri($uri){
				$this->categoryPageUri = $uri;
			}

			protected function populateItem($entry){

				$this->createAdd("category_link","HTMLLink",array(
					"link"=>$this->categoryPageUri . rawurlencode($entry->getAlias()),
					"soy2prefix"=>"cms"
				));

				$this->createAdd("category_name","CMSLabel",array(
					"text"=>$entry->getBranchName(),
					"soy2prefix"=>"cms"
				));
				
				$this->createAdd("category_alias", "CMSLabel", array(
					"text" => $entry->getAlias(),
					"soy2prefix" => "cms"
				));
			}
		}
	}

	if(!class_exists("BlogPage_EntryComponent")){
		/**
		 * 記事を表示するコンポーネント
		 */
		class BlogPage_EntryComponent extends SOYBodyComponentBase{

			var $entryPageUri;
			var $categoryPageUri;
			var $blogLabelId;
			var $categoryLabelList;

			function setCategoryPageUri($uri){
				$this->categoryPageUri = $uri;
			}

			function setEntryPageUri($uri){
				$this->entryPageUri = $uri;
			}

			function setBlogLabelId($blogLabelId){
				$this->blogLabelId = $blogLabelId;
			}

			function setCategoryLabelList($categoryLabelList){
				$this->categoryLabelList = $categoryLabelList;
			}

			function setEntry($entry){
				$link = $this->entryPageUri . rawurlencode($entry->getAlias());

				$this->createAdd("entry_id","CMSLabel",array(
					"text"=>$entry->getId(),
					"soy2prefix"=>"cms"
				));

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

				$more = $entry->getMore();

				$this->createAdd("more","CMSLabel",array(
					"html"=> '<a name="more"></a>'.$more,
					"soy2prefix"=>"cms",
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

				$this->createAdd("entry_link","HTMLLink",array(
					"soy2prefix"=>"cms",
					"link" => $link
				));

				$this->createAdd("more_link","HTMLLink",array(
					"soy2prefix"=>"cms",
					"link" => $link ."#more",
					"visible"=>(strlen($entry->getMore()) != 0)
				));

				$this->createAdd("more_link_no_anchor","HTMLLink",array(
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

				$this->createAdd("category_list","BlogPage_Entry_CategoryList",array(
					"list" => $entry->getLabels(),
					"categoryPageUri" => $this->categoryPageUri,
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
					return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]["entry_id"]; ?>','<?php echo strip_tags($'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]["title"]); ?>');
				}else{
					return parent::getStartTag();
				}
			}

		}
	}

	$page->createAdd("entry","BlogPage_EntryComponent",array(
		"soy2prefix" => "b_block",
		"entryPageUri"=> $page->getEntryPageURL(true),
		"categoryPageUri" => $page->getCategoryPageURL(true),
		"blogLabelId" => $page->page->getBlogLabelId(),
		"categoryLabelList" => $page->page->getCategoryLabelList(),
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

	if(!class_exists("BlogPage_Entry_Navigation")){
		class BlogPage_Entry_Navigation extends SOYBodyComponentBase{

			var $entryPageUri;

			function setEntryPageUri($uri){
				$this->entryPageUri = $uri;
			}

			function setEntry($entry){
				$this->createAdd("title","CMSLabel",array(
					"text" => $entry->getTitle(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("entry_link","HTMLLink",array(
					"link" => $this->entryPageUri . rawurlencode($entry->getAlias()),
					"soy2prefix" => "cms"
				));
			}
		}
	}

	$page->createAdd("next_entry","BlogPage_Entry_Navigation",array(
		"entryPageUri"=> $page->getEntryPageURL(true),
		"entry" => $next,
		"soy2prefix" => "b_block",
		"visible" => $next->getId()
	));

	$page->createAdd("prev_entry","BlogPage_Entry_Navigation",array(
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

	if(!class_exists("BlogPage_CommentForm")){
		class BlogPage_CommentForm extends HTMLForm{

			const SOY_TYPE = SOY2HTML::HTML_BODY;

			private $entryComment;

			function execute(){

				//cookieから読みだす：高速化キャッシュ対応のため廃止
				$array = array();
				//@parse_str($_COOKIE["soycms_comment"],$array);

				$this->createAdd("title","HTMLInput",array(
					"name" => "title",
					"value" => $this->entryComment->getTitle(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("author","HTMLInput",array(
					"name" => "author",
					"value" => (strlen($this->entryComment->getAuthor()) > 0) ? $this->entryComment->getAuthor() : @$array["author"],
					"soy2prefix" => "cms"
				));

				$this->createAdd("body","HTMLTextArea",array(
					"name" => "body",
					"value" => $this->entryComment->getBody(),
					"soy2prefix" => "cms"
				));

				$this->createAdd("mail_address","HTMLInput",array(
					"name" => "mail_address",
					"value" => (strlen($this->entryComment->getMailAddress()) > 0) ? $this->entryComment->getMailAddress() : @$array["mailaddress"],
					"soy2prefix" => "cms"
				));

				$this->createAdd("url","HTMLInput",array(
					"name" => "url",
					"value" => (strlen($this->entryComment->getUrl()) > 0) ? $this->entryComment->getUrl() : @$array["url"],
					"soy2prefix" => "cms"
				));

				parent::execute();
			}


			function getEntryComment() {
				return $this->entryComment;
			}
			function setEntryComment($entryComment) {
				$this->entryComment = $entryComment;
			}
		}
	}

	$page->createAdd("comment_form","BlogPage_CommentForm",array(
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

	if(!class_exists("Blog_CommentList")){
		class Blog_CommentList extends HTMLList{

			function getStartTag(){
				return '<a name="comment_list"></a>'.parent::getStartTag();
			}

			function populateItem($comment){

				$this->createAdd("title","CMSLabel",array(
					"text" => $comment->getTitle(),
					"soy2prefix" => "cms"
				));
				$this->createAdd("author","CMSLabel",array(
					"text" => $comment->getAuthor(),
					"soy2prefix" => "cms"
				));

				$comment_body = str_replace("\n","@@@@__BR__MARKER__@@@@",$comment->getBody());
				$comment_body = htmlspecialchars($comment_body, ENT_QUOTES, "UTF-8");
				$comment_body = str_replace("@@@@__BR__MARKER__@@@@","<br>",$comment_body);

				$this->createAdd("body","CMSLabel",array(
					"html" => $comment_body,
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

	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
	$commentList = $dao->getApprovedCommentByEntryId($entry->getId());

	$page->createAdd("comment_list","Blog_CommentList",array(
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

	if(!class_exists("Blog_TrackbackList")){
		class Blog_TrackbackList extends HTMLList{

			function getStartTag(){
				return '<a name="trackback_list"></a>'.parent::getStartTag();
			}

			function populateItem($trackback){

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
					"html"=> str_replace("\n","<br>", htmlspecialchars($trackback->getExcerpt(), ENT_QUOTES, "UTF-8")),
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

			}
		}
	}

	$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
	$trackbackList = $dao->getCertificatedTrackbackByEntryId($entry->getId());

	$page->createAdd("trackback_list","Blog_TrackbackList",array(
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

	if(!class_exists("Blog_TrackbackURL")){
		class Blog_TrackbackURL extends HTMLLabel{

			function execute(){

				parent::execute();

				if($this->tag == "input"){
					$this->setInnerHTML("");
				}else{
					$this->clearAttribute("value");
				}
			}
		}
	}

	$page->createAdd("trackback_link","Blog_TrackbackURL",array(
		"value" => $trackbackUrl,
		"text" => $trackbackUrl,
		"soy2prefix" => "b_block",
		"type"=>"text"
	));
}

