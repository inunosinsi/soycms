<?php

class IndexPage extends CMSUpdatePageBase{
	private $labelList;

	function __construct(){

		$this->updateCookie();

		WebPage::WebPage();

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));

		//ラベル一覧を取得
		$this->labelList = $this->getLabelList();

		$this->createAdd("label_categories","LabelCategoryList",array(
			"list" => $this->getCategorizedLabelList(),
		));

		$list = $this->run("Label.RecentLabelListAction")->getAttribute("list");
		$recent = array();
		foreach($list as $key => $value){
			if(isset($this->labelList[$value]))$recent[$key] = $this->labelList[$value];
		}

		$this->createAdd("recent_labels","RecentLabelList",array(
			"list"=>$recent
		));

		$result = $this->run("Entry.ClosedEntryListAction",array(
			"offset"=>0,"limit"=>0
		));

		$this->createAdd("closedTitle","HTMLLabel",array(
			"text"=>sprintf(CMSMessageManager::get("SOYCMS_NOT_PUBLISHED")." (%d)",$result->getAttribute("total"))
		));

		$result = $this->run("Entry.OutOfDateEntryListActoin",array(
			"offset"=>0,"limit"=>0
		));

		$this->createAdd("outofdateTitle","HTMLLabel",array(
			"text"=>sprintf(CMSMessageManager::get("SOYCMS_OUTOFDATE")." (%d)",$result->getAttribute("total"))
		));

		$result = $this->run("Entry.NoLabelEntryListAction",array(
			"offset"=>0,"limit"=>0
		));

		$this->createAdd("noLabelTitle","HTMLLabel",array(
			"text"=>sprintf(CMSMessageManager::get("SOYCMS_NO_LABELED")." (%d)",$result->getAttribute("total"))
		));

		//記事一覧を出力
		$this->outputEntryList();

		if(!UserInfoUtil::hasSiteAdminRole()){
			DisplayPlugin::hide("all_entries");
		}
//		if(!UserInfoUtil::hasEntryPublisherRole()){
//			DisplayPlugin::hide("publish");
//		}


	}

	/**
	 * クッキーに保存
	 */
	function updateCookie(){
		$path = "/";

		//Entry_Listはリセットする
		$cookieName = "Entry_List";
		$value = "";
		$timeout = 1;
		setcookie($cookieName,$value,$timeout,$path);

		//Entry_List_Limit
		if(isset($_GET['limit'])){
			$cookieName = "Entry_List_Limit";
			$value = $_GET['limit'];
			$timeout = 0;
			setcookie($cookieName,$value,$timeout,$path);
		}
	}

	/**
	 * ラベルオブジェクト一覧を取得
	 */
	function getLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.LabelListAction");
    	$result = $action->run();

    	if($result->success()){
    		return $result->getAttribute("list");
    	}else{
    		return array();
    	}
	}

	/**
	 * 分類されたラベルオブジェクト一覧を取得
	 */
	function getCategorizedLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.CategorizedLabelListAction");
    	$result = $action->run();

    	if($result->success()){
    		return $result->getAttribute("list");
    	}else{
    		return array();
    	}
	}

	/**
	 * 記事一覧を出力
	 */
	function outputEntryList(){

		$labelList = $this->labelList;

		$offset = isset($_GET['offset'])? (int)$_GET['offset'] : 0 ;
		$limit  = isset($_GET['limit'])? (int)$_GET['limit'] : ( isset($_COOKIE['Entry_List_Limit'])? (int)$_COOKIE['Entry_List_Limit'] : 10 );

		//記事を取得
		list($entries,$count,$offset) = $this->getEntries($offset,$limit);

		include_once(dirname(__FILE__).'/_EntryBlankPage.class.php');
		$this->createAdd("no_entry_message","_EntryBlankPage",array(
			"visible"=>(count($entries) == 0)
		));

		if(count($entries) > 0){
			//do nothing
		}else{
			DisplayPlugin::hide("must_exist_entry");
		}


		//自分へのリンク
		$currentLink = SOY2PageController::createLink("Entry");

		//Entry.Listへのリンク
		$listLink = SOY2PageController::createLink("Entry.List");

		//記事一覧の表を作成
		$this->createAdd("list","LabeledEntryList",array(
				"labelList"=>$labelList,
				"list" => $entries
		));

		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));

		//表示件数
		$this->createAdd("showCount10" ,"HTMLLink",array("link"=> $currentLink ."?limit=10"."#entry_list"));
		$this->createAdd("showCount20" ,"HTMLLink",array("link"=> $currentLink ."?limit=20"."#entry_list"));
		$this->createAdd("showCount50" ,"HTMLLink",array("link"=> $currentLink ."?limit=50"."#entry_list"));
		$this->createAdd("showCount100","HTMLLink",array("link"=> $currentLink ."?limit=100"."#entry_list"));
		$this->createAdd("showCount500","HTMLLink",array("link"=> $currentLink ."?limit=500"."#entry_list"));
		//フォーム
		$this->addForm("index_form",array(
			"action" => $listLink."?offset=".$offset."&limit=".$limit
		));

		HTMLHead::addScript("parameters",array(
			"lang"=>"text/JavaScript",
			"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel").'"'
		));

		//操作用のJavaScript
//		HTMLHead::addScript("entry_list",array(
//			"type" => "text/javascript",
//			"script"=> file_get_contents(dirname(__FILE__)."/script/entry_list.js")
//		));

		HTMLHead::addScript("entry_list",array(
			"type" => "text/javascript",
			"script"=> file_get_contents(dirname(__FILE__)."/script/entry_list.js")
		));

		//表示順は隠す
		DisplayPlugin::hide("no_label");
	}

	/**
	 * 記事を取得
	 * @param $offset,$limit
	 * @return (entry_array,記事の数,大きすぎた場合最終オフセット)
	 */
	function getEntries($offset,$limit){

		$action = SOY2ActionFactory::createInstance("Entry.EntryListAction",array(
			"offset"=>$offset,
			"limit"=>$limit
		));

		$result = $action->run();
		$entities = $result->getAttribute("Entities");
		$totalCount = $result->getAttribute("total");

		return array($entities,$totalCount,min($offset,$totalCount));
	}
}

class LabelCategoryList extends HTMLList{
	function populateItem($entity, $key, $index){
		$this->addLabel("label_category_name",array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));

		$toggleId = "label-".$index;
		$this->addModel("toggle_opened",array(
			"attr:id"      => "toggle_".$toggleId."_opened",
			"attr:onclick" => "return toggle_label_list(this, '".$toggleId."');"
		));
		$this->addModel("toggle_closed",array(
			"attr:id" => "toggle_".$toggleId."_closed",
			"attr:onclick" => "return toggle_label_list(this, '".$toggleId."');"
		));
		$this->addModel("toggle_target",array(
			"attr:id" => $toggleId,
		));

		$this->createAdd("label_list","LabelList",array(
			"list" => $entity,
		));
	}
}

class LabelList extends HTMLList{

	function populateItem($entity){

		$this->createAdd("label_name","HTMLLabel",array(
			//"html"  => $entity->getDisplayCaption() ." <nobr> (".(int)$entity->getEntryCount().")</nobr>",
			"html"  =>  htmlspecialchars($entity->getBranchName(),ENT_QUOTES,"UTF-8")
			          ." <nobr> (".(int)$entity->getEntryCount().")</nobr>",
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));

		$this->createAdd("label_description","HTMLLabel",array(
			"html" => nl2br(htmlspecialchars($this->trimDescription($entity->getDescription()),ENT_QUOTES,"UTF-8")),
			"title" => $entity->getDescription()
		));

		$this->createAdd("detail_link_01","HTMLLink",array(
			"title" => $entity->getCaption()." (".$entity->getEntryCount().")",
			"link"  => SOY2PageController::createLink("Entry.List")."/".$entity->getId()
		));

		$this->createAdd("create_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.Create") . "/" . $entity->getId()
		));
	}

	function trimDescription($str){

		return mb_strimwidth($str,0,96);

		$return = "";
		$tmp = "";
		for($i=0;$i<3;$i++){
			if(strlen($str)>$i*10){
				$return .= htmlspecialchars(mb_strimwidth($str,mb_strlen($tmp),32))."\n";
				$tmp .= mb_strimwidth($str,mb_strlen($tmp),20);
			}else{
//				$return .= "<br/>";
			}
		}

		return $return;

	}
}

class RecentLabelList extends HTMLList{

	function populateItem($entity){

		$this->createAdd("label_icon","HTMLImage",array(
			"src"=>$entity->getIconUrl(),
		));
		$this->createAdd("label_link","HTMLLink",array(
			"link"  => SOY2PageController::createLink("Entry.List.".$entity->getId()),
			"title" => $entity->getCaption() ." (".$entity->getEntryCount().")",
		));
		$this->createAdd("label_title","HTMLLabel",array(
			"html" => $entity->getDisplayCaption() ." <nobr>(".$entity->getEntryCount().")</nobr>",
		));

	}
}


class LabeledEntryList extends HTMLList{

	private $labelIds;
	private $labelList;

	function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
	}

	function setLabelList($list){
		$this->labelList = $list;
	}

	function populateItem($entity){
		$this->createAdd("entry_check","HTMLInput",array(
			"type"=>"checkbox",
			"name"=>"entry[]",
			"value"=>$entity->getId()
		));

		$entity->setTitle(strip_tags($entity->getTitle()));
		$title_link = SOY2HTMLFactory::createInstance("HTMLLink",array(
			"text"=>((strlen($entity->getTitle())==0)?CMSMessageManager::get("SOYCMS_NO_TITLE"):$entity->getTitle()),
			"link"=>SOY2PageController::createLink("Entry.Detail.".$entity->getId()),
			"title"=>$entity->getTitle()
		));

		$this->add("title",$title_link);

		$status = SOY2HTMLFactory::createInstance("HTMLLabel", array(
			"text" => $entity->getStateMessage()
		));

		$this->add("status", $status);

		$this->createAdd("content","HTMLLabel",array(
			"text"  => mb_strimwidth(SOY2HTML::ToText($entity->getContent()),0,100,"..."),
			"title" => mb_strimwidth(SOY2HTML::ToText($entity->getContent()),0,1000,"..."),
		));

		$displayOrder = null;
		if(method_exists($entity,'getDisplayOrder')){
			$displayOrder = $entity->getDisplayOrder();
		}

		$this->createAdd("create_date","HTMLLabel",array(
			"text" => CMSUtil::getRecentDateTimeText($entity->getCdate()),
			"title"=> date("Y-m-d H:i:s",$entity->getCdate())
		));
//		$this->createAdd("update_date","HTMLLabel",array(
//			"text" => CMSUtil::getRecentDateTimeText($entity->getUdate()),
//			"title"=> date("Y-m-d H:i:s",$entity->getUdate())
//		));

		$this->createAdd("order","HTMLInput",array(
			"type"=>"text",
			"name"=>"displayOrder[".$entity->getId()."][".$this->labelIds[0]."]",
			"value"=> $displayOrder,
			"size"=>"5"
		));

		//ラベル表示部
		$this->createAdd("label","EntryLabelList",array(
			"list" => $this->labelList,
			"entryLabelIds"=>$entity->getLabels(),
		));

	}
}

class EntryLabelList extends HTMLList{

	var $entryLabelIds = array();

	function setEntryLabelIds($list){
		if(is_array($list)){
			$this->entryLabelIds = $list;
		}
	}

	protected function populateItem($label){
		$this->createAdd("entry_list_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.List.".$label->getId()),
			"text" => "[".$label->getCaption()."]",
			"visible" => in_array($label->getId(), $this->entryLabelIds)

		));
	}
}

?>