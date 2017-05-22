<?php

class CMSBlogPage extends CMSPage{

	const MODE_TOP = "_top_";
	const MODE_ENTRY = "_entry_";
	const MODE_MONTH_ARCHIVE = "_month_";
	const MODE_CATEGORY_ARCHIVE = "_category_";
	const MODE_RSS = "_rss_";
	const MODE_POPUP = "_popup_";

	var $pageUrl;
	public $mode;
	var $year;
	var $month;
	var $day;
	var $category;
	var $entry;
	var $nextEntry;
	var $prevEntry;
	var $entries = array();
	var $label;
	public $total;
	var $offset = 0;
	var $limit;
	var $entryComment;
	var $currentAbsoluteURL;

	function doPost(){
		//comment
		if(isset($_GET["comment"])){

			$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");

			$entryComment = new EntryComment();
			$id = $this->entry->getId();
			$entryComment->setEntryId($id);
			if(isset($_POST["title"]))        $entryComment->setTitle($_POST["title"]);
			if(isset($_POST["body"]))         $entryComment->setBody($_POST["body"]);
			if(isset($_POST["author"]))       $entryComment->setAuthor($_POST["author"]);
			if(isset($_POST["mail_address"])) $entryComment->setMailAddress($_POST["mail_address"]);
			if(isset($_POST["url"]))          $entryComment->setUrl($_POST["url"]);
			$entryComment->setSubmitDate(time());

			//公開設定（自動公開/許可制）
			$blogdao = SOY2DAOFactory::create("cms.BlogPageDAO");
			try{
				$accept = $blogdao->getById($this->page->getId())->getDefaultAcceptComment();
			}catch(Exception $e){
				$accept = 0;
			}
			$entryComment->setIsApproved((boolean)$accept);

			$this->entryComment = $entryComment;

			//投稿者情報をCookieに保存
			$array = array(
				"author"      => $entryComment->getAuthor(),
				"mailaddress" => $entryComment->getMailAddress(),
				"url"         => $entryComment->getUrl()
			);
			setcookie("soycms_comment",http_build_query($array));//	,time() + (30 * 86400),'/',$_SERVER["HTTP_HOST"],true);

			try{
				//error check for length of body
				if(strlen($entryComment->getBody())==0){
					throw new Exception("");
				}

				//CMS:PLUGIN callEventFunction
				$result = CMSPlugin::callEventFunc('onSubmitComment',array("entryComment"=>$entryComment, "page" => $this),true);

				//falseでない時だけコメント挿入
				if($result !== false){
					$dao->insert($entryComment);
					//CMS:PLUGIN callEventFunction
					$result = CMSPlugin::callEventFunc('afterSubmitComment',array("entryComment"=>$entryComment, "page" => $this),true);

					//元のページにリダイレクト
					$redirect = $this->getEntryPageURL(true) . rawurlencode($this->entry->getAlias()) . "?comment_posted";
					//セッションがGETかPOSTのとき
					//TODO docomo限定
					if( ( isset($_GET[session_name()])||isset($_POST[session_name()]) ) && !isset($_COOKIE[session_name()])){
						$redirect .= "&".session_name()."=".session_id();
					}
					header("Location: ".$redirect);
					exit;
				}

			}catch(Exception $e){
				//?
			}
		}

		//tb
		if(isset($_GET["trackback"])){
			$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
			$trackback = new EntryTrackback();
			$trackback->setEntryId($this->entry->getId());
			if(isset($_POST["url"]))       $trackback->setUrl($_POST['url']);
			if(isset($_POST["blog_name"])) $trackback->setBlogName($_POST['blog_name']);
			if(isset($_POST["excerpt"]))   $trackback->setExcerpt($_POST['excerpt']);
			if(isset($_POST["title"]))     $trackback->setTitle($_POST['title']);
			$trackback->setCertification(0);
			$trackback->setSubmitdate(time());

			//公開設定（自動公開/許可制）
			$blogdao = SOY2DAOFactory::create("cms.BlogPageDAO");
			try{
				$accept = $blogdao->getById($this->page->getId())->getDefaultAcceptTrackback();
			}catch(Exception $e){
				$accept = 0;
			}
			$trackback->setCertification((boolean)$accept);

			try{
				//CMS:PLUGIN callEventFunction
				$res = CMSPlugin::callEventFunc('onSubmitTrackback',array("trackback"=>$trackback,"page" => $this),true);

				if($res !== false){
					$dao->insert($trackback);
					$res = CMSPlugin::callEventFunc('afterSubmitTrackback',array("trackback"=>$trackback,"page" => $this),true);
				}
			}catch(Exception $e){
				//失敗
				$res = false;
			}

			if($res !== false){
				$replyData = '<?xml version="1.0" encoding="utf-8"?><response><error>0</error><message>successful</message></response>';
			}else{
				$replyData = '<?xml version="1.0" encoding="utf-8"?><response><error>1</error><message>failed</message></response>';
			}

			header('Content-Type: text/xml');
			header("Content-Length: ".strlen($replyData));
			echo $replyData;
			exit;
		}

	}

	function __construct($args){

		$id = $args[0];
		$this->arguments = $args[1];
		$this->siteConfig = $args[2];

		$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
		$this->page = $pageDao->getById($id);
		$this->id = $id;

		//サイトのURL
		$this->siteUrl = $this->getSiteUrl();

		//ページのURL
		$this->pageUrl = $this->getPageUrl();

		//モードの取得、モード別の動作など
		$arguments = implode("/",$this->arguments);

		//ページの取得
		if(preg_match('/(\/?page-([0-9]*))$/',$arguments,$tmp)){
			$this->offset = $tmp[2];
			$arguments = str_replace($tmp[1],"",$arguments);
		}

		//タイトルフォーマットの取得
		$pageFormat = $this->getTitleFormat();

		if(strlen($pageFormat) == 0){
			//空っぽだったらデフォルト追加
			$pageFormat = '%BLOG%';
		}

		//モード別
		$this->mode = $this->getMode($arguments);

		switch($this->mode){
			case CMSBlogPage::MODE_ENTRY:
				if(!$this->page->getGenerateEntryFlag()){
					throw new Exception("EntryPageは表示できません");
				}
				$this->mode = CMSBlogPage::MODE_ENTRY;
				$entryId = mb_convert_encoding(
					str_replace($this->page->getEntryPageUri()."/",
							"",
							$arguments
					),
					"UTF-8",
					"UTF-8,ASCII,JIS,Shift_JIS,EUC-JP,SJIS"
				);
				list($this->entry,$this->nextEntry,$this->prevEntry) = $this->getEntry($entryId);

				//表示しているページの絶対URL
				$this->currentAbsoluteURL = $this->getEntryPageURL(true) . rawurlencode($this->entry->getAlias());

				/*
				 * Entry.idでアクセスしてきたときはエイリアスのURLに飛ばす
				 * ただし、エイリアスがEntry.idのときはそのまま
				 */
				if(
				    !isset($_GET["comment"]) && !isset($_GET["trackback"])
				    && $entryId == $this->entry->getId()
				    && $entryId != $this->entry->getAlias()
				){
					header("Location: ".$this->currentAbsoluteURL);
				}

				$pageFormat = $this->page->getEntryTitleFormat();
				$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
				$pageFormat = preg_replace('/%BLOG%/',$this->page->getTitle(),$pageFormat);
				$pageFormat = preg_replace('/%ENTRY%/',$this->entry->getTitle(),$pageFormat);
				$this->title = $pageFormat;
				break;

			case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
				if(!$this->page->getGenerateCategoryFlag()){
					throw new Exception("CategoryPageは表示できません");
				}
				$this->mode = CMSBlogPage::MODE_CATEGORY_ARCHIVE;
				$this->limit = $this->page->getCategoryDisplayCount();

				$label = mb_convert_encoding(
					str_replace(
						$this->page->getCategoryPageUri()."/",
						"",
						$arguments
					),
					"UTF-8",
					"UTF-8,ASCII,JIS,Shift_JIS,EUC-JP,SJIS"
				);

				$this->label = $this->getLabel($label);
				
				$this->entries = $this->getEntriesByLabel($this->label);

				$pageFormat = $this->page->getCategoryTitleFormat();
				$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
				$pageFormat = preg_replace('/%BLOG%/',$this->page->getTitle(),$pageFormat);
				$pageFormat = preg_replace('/%CATEGORY%/',$this->label->getCaption(),$pageFormat);
				$this->title = $pageFormat;

				//表示しているページの絶対URL
				$this->currentAbsoluteURL = $this->getCategoryPageURL(true) . rawurlencode($this->label->getAlias());

				break;

			case CMSBlogPage::MODE_MONTH_ARCHIVE:
				if(!$this->page->getGenerateMonthFlag()){
					throw new Exception("MonthPageは表示できません");
				}
				$this->mode = CMSBlogPage::MODE_MONTH_ARCHIVE;
				$this->limit = $this->page->getMonthDisplayCount();

				$date = explode("/",$arguments);
				if(strlen($this->page->getMonthPageUri())){
					array_shift($date);
				}

				$this->year  = isset($date[0]) && is_numeric($date[0]) ? (int)$date[0] : null ;
				$this->month = isset($date[1]) && is_numeric($date[1]) ? (int)$date[1] : null ;
				$this->day   = isset($date[2]) && is_numeric($date[2]) ? (int)$date[2] : null ;
				$this->entries = $this->getEntriesByDate($this->year,$this->month,$this->day);

				$pageFormat = $this->page->getMonthTitleFormat();
				$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
				$pageFormat = preg_replace('/%BLOG%/',$this->page->getTitle(),$pageFormat);
				$pageFormat = preg_replace('/%YEAR%/',$this->year,$pageFormat);
				$pageFormat = preg_replace('/%MONTH%/',$this->month,$pageFormat);
				$pageFormat = preg_replace('/%DAY%/',$this->day,$pageFormat);

				$time = mktime(0,0,0,max(1,$this->month),max(1,$this->day),$this->year);

				//条件付きフォーマット
				$pageFormat = DateLabel::ParseConditionalDateFormat($pageFormat, $time, $this->year, $this->month, $this->day);


				$this->title = $pageFormat;

				//表示しているページの絶対URL
				$this->currentAbsoluteURL = $this->getCategoryPageURL(true) . implode("/",$date);

				break;

			case CMSBlogPage::MODE_RSS:
				if(!$this->page->getGenerateRssFlag()){
					throw new Exception("RssPageは表示できません");
				}

				$this->mode = CMSBlogPage::MODE_RSS;

				$pageFormat = $this->page->getFeedTitleFormat();
				$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
				$pageFormat = preg_replace('/%BLOG%/',$this->page->getTitle(),$pageFormat);

				$charset = $this->siteConfig->getCharsetText();

				$entries = $this->getRSSEntries();
				SOY2::imports("site_include.blog.*");

				ob_start();

				switch(@$_GET["feed"]){
					case "rss":
						$content_type = "application/xml";
						soy_cms_blog_output_rss($this,$entries,$pageFormat,$charset);
						break;
					case "atom":
					default:
						$content_type = "application/atom+xml";
						soy_cms_blog_output_atom($this,$entries,$pageFormat,$charset);
						break;
				}

				$html = ob_get_contents();
				ob_end_clean();

				WebPage::__construct($args);

				$this->createAdd("feed","HTMLLabel",array(
					"html" => $html
				));

				//rss出力はここで終了
				header("Content-Type: ".$content_type."; charset=".$charset);
				return;
				break;

			case CMSBlogPage::MODE_POPUP:
				$this->mode = CMSBlogPage::MODE_POPUP;
				exit;
				break;

			case CMSBlogPage::MODE_TOP:
				if(!$this->page->getGenerateTopFlag()){
					throw new Exception("TOPPageは表示できません");
				}

				$this->mode = CMSBlogPage::MODE_TOP;
				$this->limit = $this->page->getTopDisplayCount();
				//最新エントリーを取得
				$this->entries = $this->getEntries();

				$pageFormat = $this->page->getTopTitleFormat();
				$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
				$pageFormat = preg_replace('/%BLOG%/',$this->page->getTitle(),$pageFormat);

				$this->title = $pageFormat;

				//表示しているページの絶対URL
				$this->currentAbsoluteURL = $this->getTopPageURL(true);

				break;

			default:
				throw new Exception("Invalid URL");
				break;
		}

		//記事がなかったら404
		switch($this->mode){
			case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
			case CMSBlogPage::MODE_MONTH_ARCHIVE:
			case CMSBlogPage::MODE_TOP:
			case CMSBlogPage::MODE_RSS:
				if($this->total == 0) header("HTTP/1.1 404 Not Found");
				break;
			case CMSBlogPage::MODE_ENTRY://記事ページは記事が取得できなければ例外となり404ページが表示される
			case CMSBlogPage::MODE_POPUP:
			default:
				break;
		}


		WebPage::__construct($args);
	}

	function getCacheFilePath($extension = ".html.php"){
		//ダイナミック編集では管理側にキャッシュを作るのでサイトを区別する必要がある
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE){
			 $siteId = UserInfoUtil::getSite()->getSiteId();
			 $pageUri = $siteId."/".$this->page->getUri();
		}else{
			 $pageUri = $this->page->getUri();
		}
		$cacheFileName = "cache_" . str_replace("/",".",$pageUri) . $this->mode . $extension;
		return SOY2HTMLConfig::CacheDir().$cacheFileName;
	}

	function getMode($arguments){

		$default = null;
		if(strlen($this->page->getEntryPageUri())<1)$default = CMSBlogPage::MODE_ENTRY;
		if(strlen($this->page->getCategoryPageUri())<1)$default = CMSBlogPage::MODE_CATEGORY_ARCHIVE;
		if(strlen($this->page->getMonthPageUri())<1)$default = CMSBlogPage::MODE_MONTH_ARCHIVE;
		if(strlen($this->page->getRssPageUri())<1)$default = CMSBlogPage::MODE_RSS;
		if(strlen($this->page->getTopPageUri())<1)$default = CMSBlogPage::MODE_TOP;
		
		//空の時はトップページ
		if(strlen($arguments)<1)return CMSBlogPage::MODE_TOP;
		
		switch(true){

			case (strpos($arguments,$this->page->getEntryPageUri()."/") === 0):
				return CMSBlogPage::MODE_ENTRY;
				break;

			case (strpos($arguments,$this->page->getCategoryPageUri()."/") === 0):
				return CMSBlogPage::MODE_CATEGORY_ARCHIVE;
				break;

			case (strpos($arguments,$this->page->getMonthPageUri()."/") === 0):
			case ($arguments == $this->page->getMonthPageUri()):
				return CMSBlogPage::MODE_MONTH_ARCHIVE;
				break;

			case (strpos($arguments,$this->page->getRssPageUri()) === 0):
				return CMSBlogPage::MODE_RSS;
				break;

			case (isset($_GET["popup"])):
				return CMSBlogPage::MODE_POPUP;
				break;

			case (strpos($arguments,$this->page->getTopPageUri()."/") === 0):
				return CMSBlogPage::MODE_TOP;
				break;

			case ($arguments == $this->page->getTopPageUri()):
				return CMSBlogPage::MODE_TOP;
				break;

			//use the top page for DirectoryIndex-like uri
			case $arguments == "index.html":
			case $arguments == "index.htm":
			case $arguments == "index.php":
				return CMSBlogPage::MODE_TOP;
				break;

			//return 404 if uri does not match to any type of blogpage
			default:
				//404を飛ばす前に、再度ブログページの各タイプのURIを調べる
				switch($default){
					case CMSBlogPage::MODE_ENTRY:
						if(is_null($this->page->getEntryPageUri())) return $default;
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(is_null($this->page->getCategoryPageUri())) return $default;
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
						if(is_null($this->page->getMonthPageUri())) return $default;
				}
			
				header("HTTP/1.1 404 Not Found");
				return $default;
				break;
		}

		//ここに来ることがない
		//「/」終わりじゃない時は「/」付きでリダイレクト
		if(defined("CMS_DEBUG_MODE")){
		}elseif(!defined("CMS_PREVIEW_MODE") || CMS_PREVIEW_MODE != true){
			$tmpURL = (strpos($_SERVER["REQUEST_URI"],"?") !== false) ? substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],"?")) : $_SERVER["REQUEST_URI"];
			if($tmpURL[strlen($tmpURL)-1] != "/"){
				header("Location: " . $this->getTopPageURL(true));
				exit;
			}
		}


	}

	function main(){

		if($this->mode == CMSBlogPage::MODE_RSS){
			return parent::main();
		}

		//ライブラリの読み込み
		SOY2::imports("site_include.blog.*");

		if($this->mode == CMSBlogPage::MODE_ENTRY){
			//entry
			soy_cms_blog_output_entry($this,$this->entry);

			//次のエントリー、前のエントリー
			soy_cms_blog_output_entry_navi($this,$this->nextEntry,$this->prevEntry);

			//コメントフォームを出力
			soy_cms_blog_output_comment_form($this,$this->entry,$this->entryComment);

			//トラックバックリンクを出力
			soy_cms_blog_output_trackback_link($this,$this->entry);

			//トラックバックリストを出力
			soy_cms_blog_output_trackback_list($this,$this->entry);

			//コメントリストを出力
			soy_cms_blog_output_comment_list($this,$this->entry);

		}else{
			//entry_list
			try{
				soy_cms_blog_output_entry_list($this,$this->entries);
			}catch(Exception $e){

			}

			//次のページへのリンク　next_page (next_link)
			soy_cms_blog_output_next_link($this,$this->offset,$this->limit,$this->total);

			//前のページへのリンク prev_page (prev_link)
			soy_cms_blog_output_prev_link($this,$this->offset,$this->limit);

			//記事リストのページャー
			soy_cms_blog_output_entry_list_pager($this,$this->offset,$this->limit,$this->total);

			//最初のページへのリンク first_page
			soy_cms_blog_output_first_page_link($this,$this->offset,$this->limit,$this->total);

			//最後のページへのリンク last_page
			soy_cms_blog_output_last_page_link($this,$this->offset,$this->limit,$this->total);

			//ページ数 pages
			soy_cms_blog_output_pages($this,$this->limit,$this->total);

			//現在のページ番号 current_page
			soy_cms_blog_output_current_page($this,$this->offset);

			//現在選択されているカテゴリーを出力
			soy_cms_blog_output_current_category($this);

			//現在選択されている年月日を表示
			soy_cms_blog_output_current_archive($this);

			//現在選択されている年月またはカテゴリーを表示
			soy_cms_blog_output_current_category_or_archive($this);

			//現在選択されている年月の翌月と前月へのリンク next_month
			soy_cms_blog_output_prev_next_month($this);
		}

		//カテゴリリンクを出力 category
		soy_cms_blog_output_category_link($this);

		//月別リンクを出力 archive
		soy_cms_blog_output_archive_link($this);

		//年別リンクを出力 archive_by_year
		soy_cms_blog_output_archive_link_by_year($this);

		//トップページへのリンクを出力
		soy_cms_blog_output_top_link($this);

		//最新エントリー一覧を取得
		soy_cms_blog_output_recent_entry_list($this,$this->getRecentEntries());

		//最新コメントを出力
		soy_cms_blog_output_recent_comment_list($this);

		//最新トラックバックを出力
		soy_cms_blog_output_recent_trackback_list($this);

		//feedのメタ情報を表示
		soy_cms_blog_output_meta_feed_info($this);

		//feedへのリンクを表示
		soy_cms_blog_output_feed_link($this);
		
		//メッセージの設定
		$this->createAdd("blog_name","CMSLabel",array(
			"text" => $this->page->getTitle(),
			"soy2prefix"=>"b_block")
		);
		$this->createAdd("blog_url","HTMLLink",array(
			"link" => $this->getTopPageURL(true),
			"soy2prefix"=>"b_block")
		);
		$this->createAdd("blog_description","CMSLabel",array(
			"html"=>str_replace(array("\r\n","\r","\n"),"<br />",htmlspecialchars($this->page->getDescription())),
			"soy2prefix"=>"b_block"
		));
		$this->createAdd("blog_current_absolute_url","HTMLLink",array(
			"link" => $this->currentAbsoluteURL,
			"soy2prefix"=>"b_block"
		));
		
		//開いているカテゴリページで設定したラベルのエイリアスを表示する
		$this->addLabel("category_alias", array(
			"text" => (isset($this->label)) ? $this->label->getAlias() : null,
			"soy2prefix" => "b_block"
		));

		$this->addMessageProperty("blog_name",'<?php echo $'.$this->_soy2_pageParam.'["blog_name"]; ?>');
		$this->addMessageProperty("blog_url",'<?php echo $'.$this->_soy2_pageParam.'["blog_url_attribute"]["href"]; ?>');
		$this->addMessageProperty("blog_current_absolute_url",'<?php echo $'.$this->_soy2_pageParam.'["blog_current_absolute_url_attribute"]["href"]; ?>');


		parent::main();

		$this->setTitle($this->title);

	}

	/**
	 * ページのURLを返す
	 * 末尾に必ずスラッシュを付ける
	 */
	function getPageUrl($isAbsoluteUrl = false){
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE == true){
			$pageUrl = SOY2PageController::createLink("Page.Preview")."?uri=";
			if(strlen($this->page->getUri()) >0){
				$pageUrl .= $this->page->getUri() ."/";
			}
		}else{

			//絶対パスの場合
			if($isAbsoluteUrl){
				$pageUrl = $this->siteUrl. $this->page->getUri();

			}else{
				if(strlen($this->page->getUri()) >0){
					$pageUrl = CMSPageController::createRelativeLink($this->page->getUri(), false);
				}else{
					$pageUrl = preg_replace('/\/\$/',"",CMSPageController::createRelativeLink(".", false));
				}
			}
			if(strlen($pageUrl) ==0 OR $pageUrl[strlen($pageUrl)-1] != "/") $pageUrl .= "/";
		}
		return $pageUrl;
	}
	/**
	 * トップページのURL
	 */
	function getTopPageURL($isAbsoluteUrl = false){
		$url = $this->getPageUrl($isAbsoluteUrl);
		$url .= $this->page->getTopPageURL(false);
		return $url;
	}
	/**
	 * エントリーページのURLを取得
	 */
	function getEntryPageURL($isAbsoluteUrl = false){
		$url = $this->getPageUrl($isAbsoluteUrl);
		if(strlen($this->page->getEntryPageURL()) >0){
			$url .= $this->page->getEntryPageURL(false);//末尾はスラッシュ付き
		}
		return $url;
	}
	/**
	 * カテゴリーアーカイブのURL
	 */
	function getCategoryPageURL($isAbsoluteUrl = false){
		$url = $this->getPageUrl($isAbsoluteUrl);
		if(strlen($this->page->getCategoryPageURL()) >0){
			$url .= $this->page->getCategoryPageURL(false);//末尾はスラッシュ付き
		}
		return $url;
	}
	/**
	 * 月別アーカイブのURL
	 */
	function getMonthPageURL($isAbsoluteUrl = false){
		$url = $this->getPageUrl($isAbsoluteUrl);
		if(strlen($this->page->getMonthPageURL()) >0){
			$url .= $this->page->getMonthPageURL(false);//末尾はスラッシュ付き
		}
		return $url;
	}
	/**
	 * RSSページのURL
	 */
	function getRssPageURL($isAbsoluteUrl = false){
		$url = $this->getPageUrl($isAbsoluteUrl);
		$url .= $this->page->getRssPageURL(false);
		return $url;
	}

	function getTemplate(){
		switch($this->mode){
    		case CMSBlogPage::MODE_ENTRY:
    			$template = $this->page->getEntryTemplate();
    			break;
    		case CMSBlogPage::MODE_POPUP:
    			$template = $this->page->getPopUpTemplate();
    			break;
    		case CMSBlogPage::MODE_MONTH_ARCHIVE:
    		case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
    			$template = $this->page->getArchiveTemplate();
    			break;
    		case CMSBlogPage::MODE_RSS:
    			$template = $this->getRssTemplate();
    			break;
    		case CMSBlogPage::MODE_TOP:
    		default:
    			$template = $this->page->getTopTemplate();
    			break;
    	}

		$template = $this->onLoadPageTemplate($template);
		return $this->parseComment($template);
	}

	function getTitleFormat(){
		switch($this->mode){
			case CMSBlogPage::MODE_ENTRY:
    			return $this->page->getEntryTitleFormat();
    			break;
    		case CMSBlogPage::MODE_POPUP:
    			return "";
    			break;
    		case CMSBlogPage::MODE_MONTH_ARCHIVE:
    			return $this->page->getMonthTitleFormat();
    			break;
    		case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
    			return $this->page->getCategoryTitleFormat();
    			break;
    		case CMSBlogPage::MODE_TOP:
    		default:
    			return $this->page->getTopTitleFormat();
    			break;
		}

	}

	/**
	 * エントリーをIDまたはエイリアスで取得
	 */
	function getEntry($entryId){

		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$blogLabelId = $this->page->getBlogLabelId();

		//ブログのエントリーをIDまたはエイリアスで取得
		$entry = $logic->getBlogEntry($blogLabelId,$entryId);

		//表示順の投入
		$entryLabelDAO = SOY2DAOFactory::create("cms.EntryLabelDAO");
		$entryLabel = $entryLabelDAO->getByParam($blogLabelId,$entry->getId());
		$entry->setDisplayOrder($entryLabel->getDisplayOrder());

		//コメント数、トラックバック数の投入
		$entry->setCommentCount($logic->getApprovedCommentCountByEntryId($entry->getId()));
		$entry->setTrackbackCount($logic->getCertificatedTrackbackCountByEntryId($entry->getId()));

		//ラベルの投入
		$entry->setLabels($this->getLabelsInBlog($entry));

		//前後のエントリーを取得
		$next = $logic->getNextOpenEntry($blogLabelId,$entry);
		$prev = $logic->getPrevOpenEntry($blogLabelId,$entry);

		return array($entry,$next,$prev);
	}

	/**
	 * ラベルを取得
	 */
	function getLabel($label){

		$dao = SOY2DAOFactory::create("cms.LabelDAO");

		try{
			//0から始まる場合は文字列とみなす
			if($label[0] != "0" && is_numeric($label)){
				$label = $dao->getById($label);
			}else{
				$label = $dao->getByAlias($label);
			}
		}catch(Exception $e){
			//$label = new Label();
			throw $e;
		}

		return $label;
	}

	/**
	 * エントリーを取得
	 */
	function getEntries(){
		$logic = $this->getEntryLogic();

		//表示件数を指定
		$logic->setLimit($this->page->getTopDisplayCount());
		$logic->setOffset($this->offset * $this->limit);

		//表示順の変更
		if($this->page->getTopEntrySort() == BlogPage::ENTRY_SORT_ASC){
			$logic->setReverse(true);
		}

		$entries = $logic->getOpenEntryByLabelIds(array($this->page->getBlogLabelId()));
		$this->total = $logic->getTotalCount();

		//ラベルの投入
		foreach($entries as $entry){
			$entry->setLabels($this->getLabelsInBlog($entry));
		}

		return $entries;
	}

	/**
	 * 新着記事を取得
	 *
	 * 表示件数はRSSと同じ
	 * $this->totalを汚さないためにgetRSSEntriesから分離した
	 */
	function getRecentEntries(){
		$logic = $this->getEntryLogic();

		//表示件数を指定
		$logic->setLimit($this->page->getRssDisplayCount());
		$logic->setOffset(0);

		$entries = $logic->getOpenEntryByLabelIds(array($this->page->getBlogLabelId()));

		return $entries;
	}

	/**
	 * RSS用にエントリーを取得
	 */
	function getRSSEntries(){
		$entries = $this->getRecentEntries();
		$logic = $this->getEntryLogic();
		$this->total = $logic->getTotalCount();
		return $entries;
	}

	/**
	 * ラベルを指定してエントリーを取得
	 */
	function getEntriesByLabel(Label $label){
		$logic = $this->getEntryLogic();

		//表示件数を指定
		$logic->setLimit($this->page->getCategoryDisplayCount());
		$logic->setOffset($this->offset * $this->limit);

		//表示順の変更
		if($this->page->getCategoryEntrySort() == BlogPage::ENTRY_SORT_ASC){
			$logic->setReverse(true);
		}
		
		//ブログ用のラベルIdも同時に指定して絞込み
		if($label->getId() == $this->page->getBlogLabelId()){
			$entries = $logic->getOpenEntryByLabelIds(array($label->getId()));
		}else{
			$entries = $logic->getOpenEntryByLabelIds(array($label->getId(),$this->page->getBlogLabelId()));
		}

		//ラベルの投入
		foreach($entries as $entry){
			$entry->setLabels($this->getLabelsInBlog($entry));
		}

		$this->total = $logic->getTotalCount();

		return $entries;
	}

	/**
	 * 年、月、日を指定してエントリーを取得
	 */
	function getEntriesByDate($year = null, $month = null, $day = null){
		$logic = $this->getEntryLogic();

		//表示件数を指定
		$logic->setLimit($this->page->getMonthDisplayCount());
		$logic->setOffset($this->offset * $this->limit);

		//表示順の変更
		if($this->page->getMonthEntrySort() == BlogPage::ENTRY_SORT_ASC){
			$logic->setReverse(true);
		}

		//指定がないなら今月
		if(!$year){
			list($year, $month) = explode("/", date("Y/m"));
		}

		//期間
		//2008-10-14 endは次の日または次の月の１日の00:00:00
		//           LabeledEntry::getOpenEntryByLabelIdsImplementsではendには等号は入っていない
		if(!$month){
			$start = @mktime(0,0,0,1,1,$year);
			$end = @mktime(0,0,0,1,1,$year+1);
		}elseif(!$day){
			$start = @mktime(0,0,0,$month,1,$year);
			$end = @mktime(0,0,0,$month+1,1,$year);
		}else{
			$start = @mktime(0,0,0,$month,$day,$year);
			$end = @mktime(0,0,0,$month,$day+1,$year);
		}

		$entries = $logic->getOpenEntryByLabelIds(array($this->page->getBlogLabelId()),false,$start,$end);

		//ラベルの投入
		foreach($entries as $entry){
			$entry->setLabels($this->getLabelsInBlog($entry));
		}

		$this->total = $logic->getTotalCount();

		return $entries;
	}

	/**
	 * エントリーのラベルを取得（ラベルの表示順を反映する）
	 */
	private function getLabelsInBlog($entry){
		static $_labels;
		//全ラベル：表示順に並んでいる
		if(!$_labels){
			try{
				$_labels = $this->getLabelDAO()->get();
			}catch(Exception $e){
				$_labels = array();
			}
		}

		$labels = $_labels;
		$entryLabelIds = $this->getEntryLogic()->getLabelIdsByEntryId($entry->getId());
		foreach($labels as $id => $label){
			//記事に付いていないラベル、カテゴリーと関係ないラベルを除外する
			if(!in_array($id, $entryLabelIds) || !in_array($id,$this->page->getCategoryLabelList())){
				unset($labels[$id]);
			}
		}

		return $labels;
	}

	/**
	 * RSS出力用のテンプレート
	 */
	function getRssTemplate(){
		return '<!-- soy:id="feed" /-->';
	}

	private function getEntryLogic(){
		static $logic;
		if(!$logic) $logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		return $logic;
	}

	private function getLabelDAO(){
		static $labelDAO;
		if(!$labelDAO) $labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
		return $labelDAO;
	}

}

