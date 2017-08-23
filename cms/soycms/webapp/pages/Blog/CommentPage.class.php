<?php

class CommentPage extends CMSWebPageBase{

	private $pageId;

	public function setPageId($pageId){
		$this->pageId = $pageId;
	}

	function doPost(){
		if(soy2_check_token()){
			switch($_POST['op_code']){
				case "toggleApproved":
					$result = SOY2ActionFactory::createInstance("EntryComment.ToggleApprovedAction")->run();
					if($result->success()){
						$newState = $result->getAttribute("new_state");
						if($newState){
							$this->addMessage("BLOG_COMMENT_AUTHENTICATE_SUCCESS");
						}else{
							$this->addMessage("BLOG_COMMENT_INAUTHENTICATE_SUCCESS");
						}
					}else{
						$this->addErrorMessage("BLOG_COMMENT_AUTHENTICATE_FAILED");
					}
					break;
				case "delete":
					$result = SOY2ActionFactory::createInstance("EntryComment.DeleteAction")->run();
					if($result->success()){
						$this->addMessage("BLOG_COMMENT_DELETE_SUCCESS");
					}else{
						$this->addErrorMessage("BLOG_COMMENT_DELETE_FAILED");
					}
					break;
				case "change_defaults":
					$result = $this->run("EntryComment.ChangeDefaultsAction",array("pageId"=>$this->pageId));
					if($result->success()){
						$this->addMessage("BLOG_COMMENT_DEFAULT_CHANGE_SUCCESS");
					}else{
						$this->addErrorMessage("BLOG_COMMENT_DEFAULT_CHANGE_FAILED");
					}
					break;
			}
		}

		$this->jump('Blog.Comment.'.$this->pageId);
	}

	function __construct($arg) {
		if(is_null($arg[0])){
			$this->jump('Blog');//どっかに飛ばす
		}
		$this->pageId = @$arg[0];

		parent::__construct();

		$page = $this->run("Blog.DetailAction",array("id"=>$this->pageId))->getAttribute("Page");

		/**
		 * ブログ共通メニュー
		 */
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->pageId)
		));

		/**
		 * コメント受付の標準設定フォーム
		 */
		$this->createAdd("accept_form","HTMLForm");

		$this->createAdd("default_accept","HTMLSelect",array(
			"indexOrder"=>true,
			"options"=>array(
				"0"=>CMSMessageManager::get("SOYCMS_WORD_DENY"),
				"1"=>CMSMessageManager::get("SOYCMS_WORD_ALLOW")
			),
			"name"=>"default_accept",
			"selected"=>(is_null($page->getDefaultAcceptComment()) || $page->getDefaultAcceptComment() == 0)? 0 : 1
		));


		/**
		 * 一括変更フォーム
		 */
		$this->createAdd("index_form","HTMLForm");

		/**
		 * コメントリスト
		 */
		$offset = @$_GET['offset'];
		$limit = @$_GET['limit'];

		if(is_null($offset)){
			$offset = 0;
		}
		if(is_null($limit)){
			$limit = 10;
		}

		$result = SOY2ActionFactory::createInstance("EntryComment.CommentListAction",array(
			'pageId'=>$this->pageId,
			'limit'=>$limit,
			'offset'=>$offset
		))->run();

		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}

		$list = $result->getAttribute("list");
		$count = $result->getAttribute("count");

		if(count($list)>0){
			DisplayPlugin::hide("no_comment_message");
		}else{
			DisplayPlugin::hide("must_exists_comment");
		}

		$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($page->getUri()) >0) ? $page->getUri() ."/" : "" ) ;
		$this->createAdd("comment_list","CommentList",array(
			"list" => $list,
			"url"  => $pageUrl.$page->getEntryPageUri()
		));

		/**
		 * ページャー
		 */
		$currentLink = SOY2PageController::createLink("Blog.Comment.".$this->pageId);
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		$this->createAdd("limit_10" ,"HTMLLink",array("link"=> $currentLink ."?limit=10"));
		$this->createAdd("limit_50" ,"HTMLLink",array("link"=> $currentLink ."?limit=50"));
		$this->createAdd("limit_100","HTMLLink",array("link"=> $currentLink ."?limit=100"));

		/**
		 * ツールボックス
		 */
		if($page->isActive() == Page::PAGE_ACTIVE){
			$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($page->getUri()) >0) ? $page->getUri() ."/" : "" ) ;
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_BLOGPAGE"),$pageUrl,false,"this.target = '_blank'");
		}
		CMSToolBox::addPageJumpBox();

		/**
		 * CSS
		 */
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));
		HTMLHead::addLink("comment",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/blog/comment_trackback.css")
		));

	}

}

class CommentList extends HTMLList{

	private $url;

	public function setUrl($url){
		$this->url = $url;
	}

	public function populateItem($entry){

		if(strlen($entry->getTitle()) == 0){
			$title = CMSMessageManager::get("SOYCMS_NO_TITLE");
		}else{
			$title = $entry->getTitle();
		}

		$this->createAdd("submitdate","HTMLLink",array(
			"text"	=> date('Y-m-d',$entry->getSubmitDate()),
			"link"	=> SOY2PageController::createLink("Blog.CommentDetail.".$entry->getId()),
			"title"   => date('Y-m-d H:i:s',$entry->getSubmitDate()),
			"onclick" => "return common_click_to_layer(this,{header: 'コメント詳細 - ".$title."'});"
		));

		$this->createAdd("approved","HTMLLabel",array(
				"text"=>($entry->getIsApproved() == 0)? CMSMessageManager::get("SOYCMS_WORD_DENY") : CMSMessageManager::get("SOYCMS_WORD_ALLOW"),
		));

		$this->createAdd("entry_title","HTMLLink",array(
			"html" => $this->mb_cut_length_html($entry->getEntryTitle(),18),
			"link" => $this->url."/".((strlen($entry->getAlias())) ? rawurlencode($entry->getAlias()) : $entry->getId())."#comment_list"
		));

		$hTtitle = $this->mb_cut_length_html($title,18);
		if(strlen($entry->getUrl())){
			$hTtitle = "<a href=\"".htmlspecialchars($entry->getUrl(), ENT_QUOTES, "UTF-8")."\" target=\"_blank\">{$hTtitle}</a>";
		}
		$this->createAdd("title","HTMLLabel",array(
			"html" => $hTtitle
		));

		$hAuthor = $this->mb_cut_length_html($entry->getAuthor(),18);
		if(strlen($entry->getMailAddress())){
			$hAuthor = "<a href=\"".htmlspecialchars("mailto:".$entry->getMailAddress(), ENT_QUOTES, "UTF-8")."\" >{$hAuthor}</a>";
		}
		$this->createAdd("author","HTMLLabel",array(
			"html" => $hAuthor
		));

		$this->createAdd("body","HTMLLink",array(
			"html"=>$this->mb_cut_length_html($entry->getBody(),40),
			"link"	=> SOY2PageController::createLink("Blog.CommentDetail.".$entry->getId()),
			"onclick" => "return common_click_to_layer(this,{header: 'コメント詳細 - ".$title."'});"
		));

		$this->createAdd("comment_id","HTMLInput",array(
			"value"=>$entry->getId(),
			"name"=>"comment_id[]"
		));

	}

	private function mb_cut_length_html($text,$length){
		$hText = htmlspecialchars($text, ENT_QUOTES, "UTF-8");

		if(mb_strwidth($text) > $length){
			$sText = mb_strimwidth($text,0,$length);
			$sText .= "...";

			$hText = "<span title=\"{$hText}\">".htmlspecialchars($sText, ENT_QUOTES, "UTF-8")."</span>";
		}

		return $hText;
	}
}
