<?php
function soycms_blogparts($html, $page){

	$obj = $page->create("blogparts", "HTMLTemplatePage", array(
		"arguments" => array("blogparts", $html)
	));

	if(property_exists($page, "page")){
		switch($page->page->getPageType()){
		case Page::PAGE_TYPE_BLOG:
			switch($page->mode){
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
	}

	//ブログページ
	try{
		$blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($blogPageId);
	}catch(Exception $e){
		$blog = new BlogPage();
	}

	//b_block:id="category"
	$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
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
				$counts = $logic->getOpenEntryCountByLabelIds(array_unique(array($blogLabelId,$labelId)));
			}catch(Exception $e){
				$counts= 0;
			}
			$entryCount[$labelId] = $counts;
		}
	}

	$obj->createAdd("category", "ModuleBlog_CategoryList", array(
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
		if($month == 0){
			unset($month_list[$key]);
		}
	}

	$obj->createAdd("archive","ModuleBlogPage_MonthArciveList",array(
		"list" => $month_list,
		"monthPageUri" => convertUrlOnModuleBlogParts($blog->getMonthPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="archive_every_year"
	$month_every_year_list = array();
	foreach($month_list as $key => $month){
		if($month > 0){
			$month_every_year_list[date("Y", $key)][$key] = $month;
		}
	}

	$obj->createAdd("archive_every_year", "ModuleBlogPage_MonthArciveEveryYearList", array(
		"list" => $month_every_year_list,
		"monthPageUri" => convertUrlOnModuleBlogParts($blog->getMonthPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="recent_entry_list"
	$logic->setLimit($blog->getRssDisplayCount());
	$logic->setOffset(0);

	$obj->createAdd("recent_entry_list","ModuleBlog_RecentEntryList",array(
		"list" => $logic->getOpenEntryByLabelIds(array($blogLabelId)),
		"entryPageUri"=> convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	//b_block:id="recent_comment_list"
	try{
		$comments = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic")->getRecentComments(array($blogLabelId));
	}catch(Exception $e){
		$comments = array();
	}

	$obj->createAdd("recent_comment_list","ModuleBlog_RecentCommentList",array(
		"list" => $comments,
		"entryPageUri" => convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	try{
		$trackbacks = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic")->getRecentTrackbacks(array($blogLabelId));
	}catch(Exception $e){
		$trackbacks = array();
	}

	$obj->createAdd("recent_trackback_list","ModuleBlog_RecentTrackBackList",array(
		"list" => $trackbacks,
		"entryPageUri" => convertUrlOnModuleBlogParts($blog->getEntryPageURL(true)),
		"soy2prefix" => "b_block"
	));

	$obj->display();
}

function convertUrlOnModuleBlogParts($url){
	static $siteUrl;
	if(is_null($siteUrl)){
		if(defined("SOYCMS_SITE_ID")){
			$siteId = SOYCMS_SITE_ID;
		}else{
			//SOY CMSの場合
			if(defined("_SITE_ROOT_")){
				$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
			}else{
				$siteId = UserInfoUtil::getSite()->getSiteId();
			}
		}

		$old = CMSUtil::switchDsn();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		CMSUtil::resetDsn($old);
		$siteUrl = "/";
		if(!$site->getIsDomainRoot()) $siteUrl .= $site->getSiteId() . "/";
	}
	return $siteUrl . $url;
}

class ModuleBlog_CategoryList extends HTMLList{

	private $categoryUrl;
	private $entryCount = 0;

	function setCategoryUrl($categoryUrl){
		$this->categoryUrl = $categoryUrl;
	}

	protected function populateItem($entry){

		$this->addLink("category_link", array(
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
		$this->addModel("is_current_category", array(
			"visible" => ($arg === $alias),
			"soy2prefix" => "cms"
		));
		$this->addModel("no_current_category", array(
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
	}

	function getEntryCount() {
		return $this->entryCount;
	}
	function setEntryCount($entryCount) {
		$this->entryCount = $entryCount;
	}
}

class ModuleBlogPage_MonthArciveList extends HTMLList{

 	private $monthPageUri;
	private	$format;

	function setMonthPageUri($uri){
		$this->monthPageUri = $uri;
	}

	function setFormat($format){
		$this->format = $format;
	}

	protected function populateItem($count,$key){

		$this->addLink("archive_link", array(
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

class ModuleBlogPage_MonthArciveEveryYearList extends HTMLList{

	private $monthPageUri;
	private $format;

	function setMonthPageUri($uri){
		$this->monthPageUri = $uri;
	}

	function setFormat($format){
		$this->format = $format;
	}

	protected function populateItem($month_list, $year){

		$this->addLabel("year", array(
			"text" => $year,
			"soy2prefix" => "cms"
		));

		$this->createAdd("archive","ModuleBlogPage_MonthArciveList",array(
			"list" => $month_list,
			"monthPageUri" => $this->monthPageUri,
			"secretMode" => false,
			"soy2prefix" => "cms"
		));
	}
}

class ModuleBlog_RecentEntryList extends HTMLList{

	private $entryPageUri;

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

		$this->addLink("entry_link", array(
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

class ModuleBlog_RecentCommentList extends HTMLList{

	private $entryPageUri;

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

		$this->addLink("entry_link", array(
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

		$this->addLink("url", array(
			"link" => $comment->getUrl(),
			"soy2prefix" => "cms"
		));

		$this->addLink("mail_address", array(
			"link" => "mailto:".$comment->getMailAddress(),
			"soy2prefix" => "cms"
		));
	}
}

class ModuleBlog_RecentTrackBackList extends HTMLList{

	private $entryPageUri;

	function setEntryPageUri($uri){
		$this->entryPageUri = $uri;
	}

	function populateItem($trackback){
		$link = $this->entryPageUri . rawurlencode($trackback->getAlias());

		$this->createAdd("title","CMSLabel",array(
			"text"=>$trackback->getTitle(),
			"soy2prefix" => "cms"
		));
		$this->addLink("url", array(
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
		$this->addLink("entry_link", array(
			"link"=>$link,
			"soy2prefix"=>"cms"
		));

		$this->createAdd("entry_title","CMSLabel",array(
			"text"=>$trackback->getEntryTitle(),
			"soy2prefix"=>"cms"
		));
	}
}
