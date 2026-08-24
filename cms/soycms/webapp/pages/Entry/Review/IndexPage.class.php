<?php

class IndexPage extends CMSWebPageBase{

	public function doPost(){
		if(soy2_check_token()){
			switch($_POST['op_code']){
				case 'delete':
					//削除実行
					if($this->run("Entry.RemoveAction")->success()){
						$this->addMessage("ENTRY_REMOVE_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_REMOVE_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'copy':
					//複製実行
					if($this->run("Entry.CopyAction")->success()){
						$this->addMessage("ENTRY_COPY_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_COPY_FAILED");
					}
					break;
				case 'setPublish':
					//公開状態にする
					if($this->run("Entry.PublishAction",array('publish'=>true))->success()){
						$this->addMessage("ENTRY_PUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_PUBLISH_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'setnonPublish':
					//非公開状態にする
					if($this->run("Entry.PublishAction",array('publish'=>false))->success()){
						$this->addMessage("ENTRY_NONPUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_NONPUBLISH_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'update_display':
					//表示順が押された（と判断してるけど）
					if($this->run("EntryLabel.UpdateDisplayOrderAction")->success()){
						$this->addMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_SUCCESS");
						//$this->jump("Entry.".implode(".",$this->labelIds));
					}else{
						$this->addErrorMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_FAILED");
						//$this->jump("Entry");
					}
					break;
			}
		}

		//list($entries,$count,$from,$to,$limit,$form) = self::getEntries();
		$form = "";
		$this->jump("Entry.Review".'?'.$form);
		exit;

	}

	function __construct($arg) {
		if(!CMSPlugin::activeCheck("reviewing_entries")){
			$this->jump("Entry");
		}

		parent::__construct();

		//記事を取得
		list($entries, $count) = self::getEntriesAndTotal();
		
		self::_buildSearchForm();

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));

		//ラベル一覧用のCSS
		HTMLHead::addLink("listPanel",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/listPanel.css")
		));
		HTMLHead::addLink("labelList",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/label/labelList.css")
		));

		//ラベル一覧を取得
		$labelList = $this->getLabelList();

		//自分自身へのリンク
		$currentLink = SOY2PageController::createLink("Entry.Review");

		//戻るリンクを作成
		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Entry.List") . "/"
		));

		//記事一覧の表を作成
		SOY2::import("site_include.func.public", ".php");
		$entryIds = soycms_get_entry_id_by_entries($entries);

		SOY2::import("site_include.plugin.reviewing_entries.util.REUtil");
		SOY2::import("site_include.plugin.reviewing_entries.component.ReviewingEntryListComponent");
		$this->createAdd("list", "ReviewingEntryListComponent", array(
				"list" => $entries,
				"reviewObjects" => (count($entryIds)) ? REUtil::getReviewObjects($entryIds) : array()
		));

		$this->addForm("index_form", array(
			"action" => $currentLink,
			"visible" => ($count > 0)
		));

		//表示件数変更のリンクを作成
		self::addPageLink($currentLink);

		$params = REUtil::getParameters();
		if(!is_array($params)) $params = array();

		$limit = (isset($params["limit"])) ? (int)$params["limit"] : 10;
		$offset = (isset($params["offset"])) ? (int)$params["offset"] : 0;

		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		//IE9対応

		$agent = getenv( "HTTP_USER_AGENT" );

		if(strstr($agent,"MSIE 9.0")||strstr($agent,"MSIE 8.0")){
			$this->createAdd("if_ie9","HTMLModel",array(
				"visible"=> false
			));
		}


		if($count == 0){
			$this->addMessage("ENTRY_NO_ENTRY_IS_UNDER_THE_CONDITION");
		}

		//操作用のJavaScript
		$this->addScript("entry_list", array(
			"script"=> file_get_contents(dirname(__DIR__)."/script/entry_list.js")
		));

		$this->addScript("parameters",array(
			"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel.").'"'
		));

		if(UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish_info");
		}else{
			DisplayPlugin::hide("publish");
		}
	}

	private function _buildSearchForm(){
		$params = REUtil::getParameters();
		if(!is_array($params)) $params = array();
		
		$this->addInput("freewordText", array(
			"value" => (isset($params["freeword_text"])) ? $params["freeword_text"] : ""
		));

		foreach(array("cdate", "udate") as $col){
			foreach(array("start", "end") as $typ){
				$v = (isset($params[$col][$typ])) ? $params[$col][$typ] : "";
				//formatに従っているか？yy/mm/dd
				preg_match('/[\d]{4}\/[\d]{2}\/[\d]{2}/', $v, $tmp);
				if(!isset($tmp[0])) $v = "";
				$this->addInput($col . "_" . $typ, array(
					"name" => $col."[".$typ."]",
					"value" => $v
				));
			}
		}

		$this->addSelect("blog", array(
			"name" => "blog",
			"options" => self::_blogOptions(),
			"selected" => (isset($params["blog"])) ? $params["blog"] : null
		));

		for($i = 1; $i <= 5; $i++){
			$this->addCheckBox("star_".$i, array(
				"name" => "star[]",
				"value" => $i,
				"label" => $i,
				"selected" => (isset($params["star"]) && is_numeric(array_search($i, $params["star"])))
			));
		}
		

		$this->addForm("main_form", array(
			"method" =>"get"
		));

		$this->addCheckBox("sort_type_cdate", array(
			"name" => "sort[type]",
			"value" => "cdate",
			"selected" => (isset($params["sort"]["type"]) && $params["sort"]["type"] == "cdate"),
			"label" => "作成日時"
		));

		$this->addCheckBox("sort_type_udate", array(
			"name" => "sort[type]",
			"value" => "udate",
			"selected" => (!isset($params["sort"]["type"]) || (isset($params["sort"]["type"]) && $params["sort"]["type"] == "udate")),
			"label" => "更新日時"
		));

		$this->addCheckBox("sort_sort_asc", array(
			"name" => "sort[sort]",
			"value" => "asc",
			"selected" => (isset($params["sort"]["sort"]) && $params["sort"]["sort"] == "asc"),
			"label" => "昇順"
		));

		$this->addCheckBox("sort_sort_desc", array(
			"name" => "sort[sort]",
			"value" => "desc",
			"selected" => (!isset($params["sort"]["sort"]) || (isset($params["sort"]["sort"]) && $params["sort"]["sort"] == "desc")),
			"label" => "降順"
		));
	}

	private function _blogOptions(){
		$blogs = soycms_get_hash_table_dao("blog_page")->getBlogPageList();
		if(!count($blogs)) return array();
		$_arr = array();
		foreach($blogs as $blogId => $v){
			$_arr[$blogId] = $v["title"];
		}
		return $_arr;
	}

	/**
	 * 表示件数を変更するリンクを作成
	 */
	private function addPageLink($currentLink){
		$this->addLink("showCount10" , array("link"=> $currentLink ."?limit=10"."#entry_list"));
		$this->addLink("showCount20" , array("link"=> $currentLink ."?limit=20"."#entry_list"));
		$this->addLink("showCount50" , array("link"=> $currentLink ."?limit=50"."#entry_list"));
		$this->addLink("showCount100", array("link"=> $currentLink ."?limit=100"."#entry_list"));
		$this->addLink("showCount500", array("link"=> $currentLink ."?limit=500"."#entry_list"));
	}

	private function getEntriesAndTotal(){
		SOY2::import("site_include.plugin.reviewing_entries.util.REUtil");
		if(isset($_GET["reset"])){
			REUtil::clearParameters();
			return array(array(), 0);
		}else{
			if(isset($_GET) && count($_GET)){
				$params = REUtil::getParameters();
				if(!is_array($params)) $params = array();
				foreach($_GET as $key => $v){
					$params[$key] = $v;
				}
				REUtil::saveParameters($params);
			}
		}
		$params = REUtil::getParameters();
		if(is_null($params)) return array(array(), 0);
	
		$logic = SOY2Logic::createInstance("site_include.plugin.reviewing_entries.logic.ReviewingEntrySearchLogic");
		return array($logic->search($params), $logic->getTotal($params));
	}

	private function getLabelList(){
		$result = SOY2ActionFactory::createInstance("Label.LabelListAction")->run();
		return ($result->success()) ? $result->getAttribute("list") : array();
	}
}
