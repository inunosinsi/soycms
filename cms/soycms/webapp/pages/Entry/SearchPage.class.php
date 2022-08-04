<?php

class SearchPage extends CMSWebPageBase{

	protected $labelIds;

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

		list($entries,$count,$from,$to,$limit,$form) = self::getEntries();
		$this->jump("Entry.Search".'?'.$form);
		exit;

	}

	function __construct($arg) {

		$this->labelIds = array_map(function($v) { return (int)$v; }, $arg);
		$labelIds = $this->labelIds;

		if(isset($_GET['limit'])){
			//update Cookie: Entry_List_Limit
			soy2_setcookie("Entry_List_Limit", $_GET['limit']);
		}else{
			if(isset($_COOKIE['Entry_List_Limit'])){
				$_GET['limit'] = $_COOKIE['Entry_List_Limit'];
			}
		}


		parent::__construct();

		//記事を取得
		list($entries,$count,$from,$to,$limit,$form) = self::getEntries();

		self::_buildSearchForm($form, $labelIds);

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
		if(count($this->labelIds) == 0){
			$currentLink = SOY2PageController::createLink("Entry.Search");
		}else{
			$currentLink = SOY2PageController::createLink("Entry.Search") . "/". implode("/",$this->labelIds);
		}

		//戻るリンクを作成
		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Entry.List") . "/" .implode("/",$labelIds)
		));

		//記事一覧の表を作成
		$this->createAdd("list","_component.Entry.LabeledEntryListComponent",array(
				"labelIds" => array(),
				"labelList" => $labelList,
				"list" => $entries,
				"currentLink" => $currentLink
		));

		$this->addForm("index_form", array(
			"action"=>$currentLink."?".$form,
			"visible"=>(count($_GET)>0)
		));

		//表示件数変更のリンクを作成
		self::addPageLink($currentLink, $form);

		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($form->getOffset(), $limit, $count, $currentLink .'?'. $form)
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
			"script"=> file_get_contents(dirname(__FILE__)."/script/entry_list.js")
		));

		if(count($labelIds) == 0 || !is_numeric(implode("",$labelIds))){
			$this->addScript("parameters",array(
				"lang"=>"text/JavaScript",
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel").'"'
			));
		}else{
			$this->addScript("parameters",array(
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel.".implode('.',$labelIds)).'"'
			));
		}

		$this->addCheckBox("label_op_and", array(
			"type" => "radio",
			"value" => "AND",
			"selected" => is_null($form->getLabelOperator()) || $form->getLabelOperator() == "AND",
			"name" => "labelOperator",
			"label" => "AND"
		));

		$this->addCheckBox("label_op_or", array(
			"type" => "radio",
			"value" => "OR",
			"selected" => !(is_null($form->getLabelOperator()) || $form->getLabelOperator() == "AND"),
			"name" => "labelOperator",
			"label" => "OR"
		));

		if(UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish_info");
		}else{
			DisplayPlugin::hide("publish");
		}
	}

	private function _buildSearchForm(SearchActionForm $form, array $labelIds){
		$this->createAdd("label_list","_component.Entry.SearchLabelListComponent",array(
			"list"=>$this->run("Label.LabelListAction")->getAttribute("list"),
			"selectedIds"=>array_merge($form->getLabel(),$labelIds)
		));

		$this->addInput("freewordText", array(
			"value" => (isset($_GET["freeword_text"])) ? $_GET["freeword_text"] : ""
		));

		foreach(array("cdate", "udate") as $col){
			foreach(array("start", "end") as $typ){
				$v = (isset($_GET[$col][$typ])) ? $_GET[$col][$typ] : "";
				//formatに従っているか？yy/mm/dd
				preg_match('/[\d]{4}\/[\d]{2}\/[\d]{2}/', $v, $tmp);
				if(!isset($tmp[0])) $v = "";
				$this->addInput($col . "_" . $typ, array(
					"name" => $col."[".$typ."]",
					"value" => $v
				));
			}
		}
		

		$this->addForm("main_form", array(
			"method" =>"get"
		));

		//カスタムフィールドアドバンスド
		$cfaItems = self::_getCustomFieldItems();
		$cfaCnt = count($cfaItems);
		DisplayPlugin::toggle("customfield_advanced_items", $cfaCnt);

		$this->createAdd("customfield_list", "_component.Entry.SearchCustomfieldListComponent", array(
			"list" => $cfaItems,
			"conditions" => (isset($_GET["customfield"]) && is_array($_GET["customfield"])) ? $_GET["customfield"] : array(),
			"last" => $cfaCnt
		));

		$this->addCheckBox("sort_type_cdate", array(
			"name" => "sort[type]",
			"value" => "cdate",
			"selected" => (isset($_GET["sort"]["type"]) && $_GET["sort"]["type"] == "cdate"),
			"label" => "作成日時"
		));

		$this->addCheckBox("sort_type_udate", array(
			"name" => "sort[type]",
			"value" => "udate",
			"selected" => (!isset($_GET["sort"]["type"]) || (isset($_GET["sort"]["type"]) && $_GET["sort"]["type"] == "udate")),
			"label" => "更新日時"
		));

		$this->addCheckBox("sort_sort_asc", array(
			"name" => "sort[sort]",
			"value" => "asc",
			"selected" => (isset($_GET["sort"]["sort"]) && $_GET["sort"]["sort"] == "asc"),
			"label" => "昇順"
		));

		$this->addCheckBox("sort_sort_desc", array(
			"name" => "sort[sort]",
			"value" => "desc",
			"selected" => (!isset($_GET["sort"]["sort"]) || (isset($_GET["sort"]["sort"]) && $_GET["sort"]["sort"] == "desc")),
			"label" => "降順"
		));
	}

	private function _getCustomFieldItems(){
		if(!file_exists(UserInfoUtil::getSiteDirectory() . ".plugin/CustomFieldAdvanced.active")) return array();
		if(!class_exists("CustomFieldPluginAdvanced")) include(SOY2::rootDir() . "site_include/plugin/CustomFieldAdvanced/CustomFieldAdvanced.php");

		$advObj = CMSPlugin::loadPluginConfig("CustomFieldAdvanced");
		if(!count($advObj->customFields)) return array();
				
		$items = array();
		foreach($advObj->customFields as $fieldId => $field){
			if(!$field->getIsSearchItem()) continue;
			$items[$fieldId] = $field->getLabel();
		}

		return $items;
	}

	/**
	 * 表示件数を変更するリンクを作成
	 */
	private function addPageLink($currentLink, SearchActionForm $form){
		$this->addLink("showCount10" , array("link"=> $currentLink ."?limit=10"."#entry_list"));
		$this->addLink("showCount20" , array("link"=> $currentLink ."?limit=20"."#entry_list"));
		$this->addLink("showCount50" , array("link"=> $currentLink ."?limit=50"."#entry_list"));
		$this->addLink("showCount100", array("link"=> $currentLink ."?limit=100"."#entry_list"));
		$this->addLink("showCount500", array("link"=> $currentLink ."?limit=500"."#entry_list"));
	}

	/**
	 * @return array(表示する記事,合計件数,from,to)
	 */
	private function getEntries(){
		$result = $this->run("Entry.SearchAction");
		return array(
			$result->getAttribute("Entities"),
			$result->getAttribute("total"),
			$result->getAttribute("from"),
			$result->getAttribute("to"),
			$result->getAttribute("limit"),
			$result->getAttribute("form")
		);
	}

	private function getLabelList(){
		$result = SOY2ActionFactory::createInstance("Label.LabelListAction")->run();
		return ($result->success()) ? $result->getAttribute("list") : array();
	}
}
