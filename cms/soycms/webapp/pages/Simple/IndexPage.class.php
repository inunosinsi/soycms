<?php

class IndexPage extends CMSWebPageBase{

	var $blogIds;

	function __construct(){

		parent::__construct();

		$this->addLabel("widgets", array(
			"html" => self::_getWidgetsHTML()
		));

		HTMLHead::addLink("avav",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/dashboard.css")."?".SOYCMS_BUILD_TIME
		));

		$this->createAdd("recentEntries","_component.Simple.RecentEntryListComponent",array(
			"list"   => $this->run("Entry.RecentListAction",array("limit" => 10))->getAttribute("list"),
			"labels" => self::_getLabelList(),
		));


		//最近のコメントを出力
		SOY2::import("domain.cms.BlogPage");
		self::_outputCommentList();
		self::_outputTrackbackList();

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")."?".SOYCMS_BUILD_TIME
		));

		//記事操作周りを出力
		self::_outputEntryLink();
	}

	private function _getWidgetsHTML(){
		$list = $this->run("Plugin.PluginListAction")->getAttribute("plugins");

		$box = array(array(),array(),array());

		$counter = 0;
		foreach($list as $plugin){
			if(!$plugin->getCustom()) continue;
			if(!$plugin->isActive()) continue;

			$customs = $plugin->getCustom();

			$id = $plugin->getId();
			$html = "<div class=\"widget_top\">".$plugin->getName()."</div>";
			$html .= "<div class=\"widget_middle\">";

			foreach($customs as $mkey => $custom){
				if($custom["func"]){
						$html .= '<iframe src="'.SOY2PageController::createLink("Plugin.CustomPage").'?id='.$id.'&menuId='.$mkey.'"' .
							' style="width:230px;border:0;" frameborder="no"></iframe>';
				}else{
					$html .= $custom["html"];
				}
			}

			$html.= "</div>";
			$html.= "<div class=\"widget_bottom\"></div>";

			$box[$counter][] = $html;

			$counter++;
			if($counter > 2)$counter = 0;
		}

		$widgets = "<table><tr>";
		foreach($box as $key => $htmls){
			$widgets .= "<td id=\"widigets_$key\" style=\"width:245px;vertical-align:top;\">";
			$widgets .= implode("",$htmls);
			$widgets .= "</td>";
		}
		$widgets .= "</tr></table>";

		return $widgets;
	}

	private function _outputCommentList(){

		$blogArray = self::_getBlogIds();
		$blogIds = array_keys($blogArray);

		$comments = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic")->getComments($blogIds,3,0);

		if(count($comments) == 0){
			DisplayPlugin::hide("only_comment_exists");
		}

		foreach($comments as $key => $comment){
			$comment->info = self::_getBlogId($comment->getEntryId());
		}

		$this->createAdd("recentComment","_component.Simple.RecentCommentListComponent",array(
			"list" => $comments
		));
	}

	private function _outputTrackbackList(){

		$blogArray = self::_getBlogIds();
		$blogIds = array_keys($blogArray);

		$trackbacks = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic")->getByLabelIds($blogIds,3,0);

		if(count($trackbacks) == 0){
			DisplayPlugin::hide("only_trackback_exists");
		}

		foreach($trackbacks as $key => $trackback){
			$trackbacks[$key]->info = self::_getBlogId($trackback->getEntryId());
		}

		$this->createAdd("recentTrackback","_component.Simple.RecentTrackbackListComponent",array(
			"list" => $trackbacks
		));
	}

	private function _getBlogIds(){
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

	private function _getBlogId($entryId){

		$blogIds = self::_getBlogIds();

		$entry = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getById($entryId);
		$labels = $entry->getLabels();

		foreach(array_keys($blogIds) as $blogId){
			if(in_array($blogId, $labels)){
				return array("blog" => $blogIds[$blogId], "entry" => $entry);
			}
		}
	}

	private function _outputEntryLink(){

		//ラベル一覧を取得
		$this->labelList = self::_getLabelList();

		$list = $this->run("Label.RecentLabelListAction")->getAttribute("list");
		$recent = array();
		foreach($list as $key => $value){
			if(isset($this->labelList[$value]))$recent[$key] = $this->labelList[$value];
		}

		$this->createAdd("recent_labels","_component.Simple.RecentLabelListComponent",array(
			"list"=>$recent
		));
	}

	/**
	 * ラベルオブジェクト一覧を取得
	 * page/Entry/IndexPage.class.phpからコピー
	 */
	private function _getLabelList(){
		$result = SOY2ActionFactory::createInstance("Label.LabelListAction")->run();
		if($result->success()){
			return $result->getAttribute("list");
		}else{
			return array();
		}
	}
}
