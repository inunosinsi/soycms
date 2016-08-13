<?php

class IndexPage extends CMSWebPageBase{

	function doPost(){

		$dir = UserInfoUtil::getSiteDirectory() . "/.cache/";

		$files = scandir($dir);

		foreach($files as $file){

			if($file[0] == ".") continue;

			unlink($dir . $file);

		}

		$this->jump("Index");
	}
	
	var $blogIds;

	function __construct(){
		
		//記事管理者以上の時
		if(UserInfoUtil::hasSiteAdminRole()){
    	
    		$initDetect = $this->run("Init.InitDetectAction");
			if($initDetect->success()){
				
				if($initDetect->getAttribute("detect")){
					// 初めてサイトにアクセスする場合は２択ページに飛ぶ
					if(CMSUtil::checkZipEnable(true)){
						$this->jump("Init");
					}else{
						//ただしzipが解凍できない場合は以前のウィザード
						$this->jump("Wizard"); 
					}
					exit;
				}
			}
		}

		if(!UserInfoUtil::hasSiteAdminRole()){
			SOY2PageController::jump("Simple");
		}

		WebPage::__construct();

		$this->addLabel("widgets", array(
			"html" => $this->getWidgetsHTML()
		));

		HTMLHead::addLink("dashboard", array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/dashboard.css")."?".SOYCMS_BUILD_TIME
		));

		$result = $this->run("Entry.RecentListAction");

		$this->createAdd("recentEntries", "RecentEntryList", array(
			"list"=>$result->getAttribute("list"),
			"labels"=>$result->getAttribute("labels")
		));

		$this->createAdd("recentPage", "RecentPageList", array(
			"list" => $this->run("Page.RecentPageListAction")->getAttribute("list")
		));

		$result = $this->run("Page.PageListAction", array("buildTree" => true));
		$options = $result->getAttribute("PageTree");

		$this->addSelect("page_tree", array(
			"options"=>$options,
			"indexOrder"=>true,
			"onchange"=>"location.href='" . SOY2PageController::createLink("Page.Detail.") . "'+this.value;"
		));

		//最近のコメントを出力
		SOY2::import("domain.cms.BlogPage");
		$this->outputCommentList();
		$this->outputTrackbackList();
	}

	function getWidgetsHTML(){
		$result = $this->run("Plugin.PluginListAction");
		$list = $result->getAttribute("plugins");

		$box = array(array(), array(), array());

		$counter = 0;
		foreach($list as $plugin){
			if(!$plugin->getCustom()) continue;
			if(!$plugin->isActive()) continue;
			
			$customs = $plugin->getCustom();

			$id = $plugin->getId();
			$html = "<div class=\"widget_top\">" . $plugin->getName() . "</div>";
			$html .= "<div class=\"widget_middle\">";

			foreach($customs as $mkey => $custom){
				if($custom["func"]){
						$html .= '<iframe src="' . SOY2PageController::createLink("Plugin.CustomPage") . '?id=' . $id . '&menuId=' . $mkey . '"' .
							' style="width:230px;border:0;" frameborder="no"></iframe>';
				}else{
					$html .= $custom["html"];
				}
			}

			$html.= "</div>";
			$html.= "<div class=\"widget_bottom\"></div>";
			
			$box[$counter][] = $html;
			
			$counter++;
			if($counter > 2) $counter = 0;
		}
		
		$widgets = "<table><tr>";
		foreach($box as $key => $htmls){
			$widgets .= "<td id=\"widigets_$key\" style=\"width:245px;vertical-align:top;\">";
			$widgets .= implode("", $htmls);
			$widgets .= "</td>";
		}
		$widgets .= "</tr></table>";

		return $widgets;
	}

	function outputCommentList(){

		$blogArray = $this->getBlogIds();
		$blogIds = array_keys($blogArray);

		$commentListLogic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
		$comments = $commentListLogic->getComments($blogIds, 3, 0);

		if(count($comments) == 0){
			DisplayPlugin::hide("only_comment_exists");
		}

		foreach($comments as $key => $comment){
			$comment->info = $this->getBlogId($comment->getEntryId());
		}

		$this->createAdd("recentComment", "RecentCommentList", array(
			"list"=>$comments
		));
	}

	function outputTrackbackList(){
		
		$blogArray = $this->getBlogIds();
		$blogIds = array_keys($blogArray);
		
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic");

		$trackbacks = $logic->getByLabelIds($blogIds, 3, 0);

		if(count($trackbacks) == 0){
			DisplayPlugin::hide("only_trackback_exists");
		}

		foreach($trackbacks as $key => $trackback){
			$trackbacks[$key]->info = $this->getBlogId($trackback->getEntryId());
		}
		
		$this->createAdd("recentTrackback", "RecentTrackbackList", array(
			"list"=>$trackbacks
		));
	}
	
	function getBlogIds(){
		if(is_null($this->blogIds)){
			$blogs = $this->run("Blog.BlogListAction")->getAttribute("list");
			$this->blogIds = array();

			foreach($blogs as $blog){
				if(!is_null($blog->getBlogLabelId())){
					$this->blogIds[$blog->getBlogLabelId()] = $blog;
				}
			}
		}
		
		return $this->blogIds;
	}

	function getBlogId($entryId){

		$blogIds = $this->getBlogIds();
		
		$entryLogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$entry = $entryLogic->getById($entryId);

		$labels = $entry->getLabels();

		foreach(array_keys($blogIds) as $blogId){
			if(in_array($blogId, $labels)){
				return array("blog"=>$blogIds[$blogId], "entry" => $entry);
			}
		}
	}
}

class RecentCommentList extends HTMLList{

	function populateItem($entity){
		$blog = (isset($entity->info["blog"])) ? $entity->info["blog"] : null;
		$entry = (isset($entity->info["entry"])) ? $entity->info["entry"] : null;

		if(is_null($blog)) $blog = new BlogPage();
		if(is_null($entry)) $entry = new Entry();

		$title = ((strlen($entity->getTitle())==0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle());
		$title .= strlen($entity->getAuthor()) == 0  ? "" : " (" . $entity->getAuthor() . ")";

		$this->addLink("title", array(
			"link"=>SOY2PageController::createLink("Blog.Comment." . $blog->getId()),
			"text"=>$title
		));

		$this->addLabel("content", array(
			"text"=>$entry->getTitle() . " (" . $blog->getTitle() . ")"
		));
		$this->addLabel("udate", array(
			"text"=>CMSUtil::getRecentDateTimeText($entity->getSubmitDate()),
			"title" => date("Y-m-d H:i:s", $entity->getSubmitDate())
		));
	}
}

class RecentTrackbackList extends HTMLList{

	function populateItem($entity){
		$blog = (isset($entity->info["blog"])) ? $entity->info["blog"] : null;
		$entry = (isset($entity->info["entry"])) ? $entity->info["entry"] : null;

		if(is_null($blog)) $blog = new BlogPage();
		if(is_null($entry)) $entry = new Entry();


		$title = ((strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle());
		$title .= (strlen($entity->getBlogName()) == 0)  ? "" : " (" . $entity->getBlogName() . ")";

		$this->addLink("title", array(
			"link"=>SOY2PageController::createLink("Blog.Trackback." . $blog->getId()),
			"text"=>$title
		));
		$this->addLabel("content", array(
			"text"=>$entry->getTitle() . " (" . $blog->getTitle() . ")"
		));
		$this->addLabel("udate", array(
			"text"  => CMSUtil::getRecentDateTimeText($entity->getSubmitDate()),
			"title" => date("Y-m-d H:i:s", $entity->getSubmitDate())
		));
	}

}

class RecentEntryList extends HTMLList{

	var $labels = array();

	function setLabels($array){
		if(is_array($array)){
			$this->labels = $array;
		}
	}

	function populateItem($entity){

		$this->addLink("title", array(
			"link" => SOY2PageController::createLink("Entry.Detail") . "/" . $entity->getId(),
			"text" => (strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
			"onmouseover" => 'var ele=$(\'#popup_entry_comment_' . $entity->getId() . '\');if(!ele)return;ele.show();',
			"onmouseout" => 'var ele=$(\'#popup_entry_comment_' . $entity->getId() . '\');if(!ele)return;ele.hide();',
		));

		$popupText = ($entity->getDescription()) ? CMSUtil::getText("[メモ]") . $entity->getDescription() : "";
		$this->addLabel("popup", array(
			"id" => "popup_entry_comment_" . $entity->getId(),
			"text" => $popupText,
			"visible" => strlen($popupText)
		));
		
		$this->addLabel("content", array(
			"text"  => SOY2HTML::ToText($entity->getContent()),
			"width" => 60,
			"title" => mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 1000, "..."),
		));


		$this->addLabel("udate", array(
			"text"  => CMSUtil::getRecentDateTimeText($entity->getUdate()),
			"title" => date("Y-m-d H:i:s", $entity->getUdate())
		));
	}
}

class RecentPageList extends HTMLList{
	
	function populateItem($entity){
		
		$this->addLink("title", array(
			"text"=>(strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
			"link"=>SOY2PageController::createLink("Page.Detail.") . $entity->getId()
		));

		$this->addLink("content", array(
			"text" => "/" . $entity->getUri(),
			"link" => CMSUtil::getSiteUrl() . $entity->getUri()
		));
		
		$this->addLabel("udate", array(
			"text"=>CMSUtil::getRecentDateTimeText($entity->getUdate()),
			"title" => date("Y-m-d H:i:s", $entity->getUdate())
		));
	}	
}
?>