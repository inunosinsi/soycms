<?php

class IndexPage extends CMSUpdatePageBase{
	private $labelList;

	function __construct(){

		$this->updateCookie();

		parent::__construct();

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));

		//ラベル一覧を取得
		$this->labelList = $this->getLabelList();

		$this->createAdd("label_categories","_component.Entry.LabelCategoryListComponent",array(
			"list" => $this->getCategorizedLabelList(),
		));

		//ラベル一覧の表示・非表示をCookieの値で切り替える
		$this->addModel("entries-by-label",array(
			"class" => "panel-collapse collapse".(isset($_COOKIE['entry-index-label-panel-status']) && $_COOKIE['entry-index-label-panel-status'] == 'closed' ? '' : ' in'),
		));

		$list = $this->run("Label.RecentLabelListAction")->getAttribute("list");
		$recent = array();
		foreach($list as $key => $value){
			if(isset($this->labelList[$value]))$recent[$key] = $this->labelList[$value];
		}

		$this->createAdd("recent_labels","_component.Entry.RecentLabelListComponent",array(
			"list"=>$recent
		));

		//公開状態別の表示・非表示をCookieの値で切り替える
		$this->addModel("entries-by-status",array(
			"class" => "panel-collapse collapse".(isset($_COOKIE['entry-index-status-panel-status']) && $_COOKIE['entry-index-status-panel-status'] == 'closed' ? '' : ' in'),
		));

		$result = $this->run("Entry.ClosedEntryListAction",array(
			"offset"=>0,"limit"=>0
		));
		$this->addLabel("entries_count_unpublished",array(
			"text" => ( (int)$result->getAttribute("total")),
		));

		$result = $this->run("Entry.OutOfDateEntryListActoin",array(
			"offset"=>0,"limit"=>0
		));
		$this->addLabel("entries_count_outofdate",array(
				"text" => ( (int)$result->getAttribute("total")),
		));

		$result = $this->run("Entry.NoLabelEntryListAction",array(
			"offset"=>0,"limit"=>0
		));
		$this->addLabel("entries_count_without_label",array(
				"text" => ( (int)$result->getAttribute("total")),
		));

		//記事一覧を出力
		$this->outputEntryList();

		if(!UserInfoUtil::hasSiteAdminRole()){
			DisplayPlugin::hide("all_entries");
		}

		//これがコメントアウトされているのはなぜなのか
//		if(!UserInfoUtil::hasEntryPublisherRole()){
//			DisplayPlugin::hide("publish");
//		}


	}

	/**
	 * クッキーに保存
	 */
	function updateCookie(){
		//Entry_Listはリセットする
		soy2_setcookie("Entry_List");

		//Entry_List_Limit
		if(isset($_GET['limit'])){
			soy2_setcookie("Entry_List_Limit", $_GET['limit']);
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
		list($entries,$count,$offset) = self::getEntries($offset,$limit);

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
		$this->createAdd("list","_component.Entry.LabeledEntryListComponent",array(
				"labelList"=>$labelList,
				"list" => $entries
		));

		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		//表示件数
		$this->addLink("showCount10" , array("link"=> $currentLink ."?limit=10"."#entry_list"));
		$this->addLink("showCount20" , array("link"=> $currentLink ."?limit=20"."#entry_list"));
		$this->addLink("showCount50" , array("link"=> $currentLink ."?limit=50"."#entry_list"));
		$this->addLink("showCount100", array("link"=> $currentLink ."?limit=100"."#entry_list"));
		$this->addLink("showCount500", array("link"=> $currentLink ."?limit=500"."#entry_list"));
		//フォーム
		$this->addForm("index_form",array(
			"action" => $listLink."?offset=".$offset."&limit=".$limit
		));

		$this->addScript("parameters", array(
			"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel").'"'
		));

		//操作用のJavaScript
		$this->addScript("entry_list",array(
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
	private function getEntries($offset,$limit){
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
