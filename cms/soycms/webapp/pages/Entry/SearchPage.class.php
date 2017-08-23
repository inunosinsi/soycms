<?php

class SearchPage extends CMSWebPageBase{

	protected $labelIds;

	public function doPost(){
		if(soy2_check_token()){
			switch($_POST['op_code']){
				case 'delete':
					//削除実行
					$result = $this->run("Entry.RemoveAction");
					if($result->success()){
						$this->addMessage("ENTRY_REMOVE_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_REMOVE_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'copy':
					//複製実行
					$result = $this->run("Entry.CopyAction");
					if($result->success()){
						$this->addMessage("ENTRY_COPY_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_COPY_FAILED");
					}
					break;
				case 'setPublish':
					//公開状態にする
					$result =$this->run("Entry.PublishAction",array('publish'=>true));
					if($result->success()){
						$this->addMessage("ENTRY_PUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_PUBLISH_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'setnonPublish':
					//非公開状態にする
					$result = $this->run("Entry.PublishAction",array('publish'=>false));
					if($result->success()){
						$this->addMessage("ENTRY_NONPUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_NONPUBLISH_FAILED");
					}
					//$this->jump("Entry.List.".implode(".",$this->labelIds));
					break;
				case 'update_display':
					//表示順が押された（と判断してるけど）
					$result = $this->run("EntryLabel.UpdateDisplayOrderAction");

					if($result->success()){
						$this->addMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_SUCCESS");
						//$this->jump("Entry.".implode(".",$this->labelIds));
					}else{
						$this->addErrorMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_FAILED");
						//$this->jump("Entry");
					}
					break;
			}
		}

		list($entries,$count,$from,$to,$limit,$form) = $this->getEntries();
		$this->jump("Entry.Search".'?'.$form);
		exit;

	}

	function __construct($arg) {

		$this->labelIds = array_map(create_function('$v','return (int)$v;'),$arg);
		$labelIds = $this->labelIds;

		if(isset($_GET['limit'])){
			//update Cookie: Entry_List_Limit
			$cookieName = "Entry_List_Limit";
			$value = $_GET['limit'];
			$timeout = 0;
			$path = "/";
			setcookie("Entry_List_Limit",$value,$timeout,$path);
		}else{
			if(isset($_COOKIE['Entry_List_Limit'])){
				$_GET['limit'] = $_COOKIE['Entry_List_Limit'];
			}
		}


		parent::__construct();

		//記事を取得
		list($entries,$count,$from,$to,$limit,$form) = $this->getEntries();

		$result = $this->run("Label.LabelListAction");
		$this->createAdd("label_list","SearchLabelList",array(
			"list"=>$result->getAttribute("list"),
			"selectedIds"=>array_merge($form->label,$labelIds)
		));

		$this->createAdd("freewordText","HTMLInput",array(
			"value"=>@$_GET["freeword_text"]
		));

		$this->createAdd("main_form","HTMLForm",array(
			"method"=>"get"
		));

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
		$this->createAdd("back_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.List") . "/" .implode("/",$labelIds)
		));

		//記事一覧の表を作成
		$this->createAdd("list","LabeledEntryList",array(
				"labelIds"=>array(),
				"labelList"=>$labelList,
				"list" => $entries,
				"currentLink" => $currentLink
		));

		$this->createAdd("index_form","HTMLForm",array(
			"action"=>$currentLink."?".$form,
			"visible"=>(count($_GET)>0)
		));

		//表示件数変更のリンクを作成
		$this->addPageLink($currentLink, $form);

		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($form->offset, $limit, $count, $currentLink .'?'. $form)
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
		$this->addScript("entry_list",array(
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

		$this->createAdd("label_op_and","HTMLCheckBox",array(
			"type"=>"radio",
			"value"=>"AND",
			"selected"=>is_null($form->labelOperator) || $form->labelOperator == "AND",
			"name"=>"labelOperator",
			"label"=>"AND"
		));

		$this->createAdd("label_op_or","HTMLCheckBox",array(
			"type"=>"radio",
			"value"=>"OR",
			"selected"=>!(is_null($form->labelOperator) || $form->labelOperator == "AND"),
			"name"=>"labelOperator",
			"label"=>"OR"
		));


		if(UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish_info");
		}else{
			DisplayPlugin::hide("publish");
		}


	}

	/**
	 * 表示件数を変更するリンクを作成
	 */
	private function addPageLink($currentLink, SearchActionForm $form){
		$limit = $form->limit;

		$form->limit = 10;
		$this->createAdd("showCount10" ,"HTMLLink",array("link"=> $currentLink ."?".$form));

		$form->limit = 20;
		$this->createAdd("showCount20" ,"HTMLLink",array("link"=> $currentLink ."?".$form));

		$form->limit = 50;
		$this->createAdd("showCount50" ,"HTMLLink",array("link"=> $currentLink ."?".$form));

		$form->limit = 100;
		$this->createAdd("showCount100" ,"HTMLLink",array("link"=> $currentLink ."?".$form));

		$form->limit = 500;
		$this->createAdd("showCount500" ,"HTMLLink",array("link"=> $currentLink ."?".$form));

		$form->limit = $limit;//元に戻す。
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
		$action = SOY2ActionFactory::createInstance("Label.LabelListAction");
		$result = $action->run();

		if($result->success()){
			$list = $result->getAttribute("list");
			return $list;
		}else{
			return array();
		}
	}
}

class SearchLabelList extends HTMLList{
	private $selectedIds = array();

	public function setSelectedIds($ids){
		$this->selectedIds = $ids;
		if(!is_array($this->selectedIds)){
			$this->selectedIds = array();
		}
	}
	protected function populateItem($entity){

		$elementID = "label_".$entity->getId();

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"=>"label[]",
			"value"=>$entity->getId(),
			"selected"=>in_array($entity->getId(),$this->selectedIds),
			"elementId" => $elementID,
		));

		$this->createAdd("label_label","HTMLModel",array(
			"for"=>$elementID,
		));

		$this->createAdd("label_name","HTMLLabel",array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";"
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src"=>$entity->getIconUrl()
		));

	}

}
class LabelList extends HTMLList{

	var $entryLabelIds = array();

	public function setEntryLabelIds($list){
		if(is_array($list)){
			$this->entryLabelIds = $list;
		}
	}

	protected function populateItem($label){
		$this->createAdd("entry_list_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Entry.List.".$label->getId()),
			"text" => $label->getCaption(),
			"visible" => in_array($label->getId(), $this->entryLabelIds),
			"style"=> "color:#" . sprintf("%06X",$label->getColor()).";"
			."background-color:#" . sprintf("%06X",$label->getBackgroundColor()).";",

		));
	}
}

class LabeledEntryList extends HTMLList{

	private $labelIds;
	private $labelList;

	public function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
	}

	public function setLabelList($list){
		$this->labelList = $list;
	}

	protected function populateItem($entity){
		$this->createAdd("entry_check","HTMLInput",array(
			"type"=>"checkbox",
			"name"=>"entry[]",
			"value"=>$entity->getId()
		));

		$entity->setTitle(strip_tags($entity->getTitle()));
		$title_link = SOY2HTMLFactory::createInstance("HTMLLink",array(
			"text"=>((strlen($entity->getTitle())==0)?CMSMessageManager::get("SOYCMS_NO_TITLE"):$entity->getTitle()),
			"link"=>SOY2PageController::createLink("Entry.Detail.".$entity->getId())
		));

		$this->add("title",$title_link);

		$status = SOY2HTMLFactory::createInstance("HTMLLabel", array(
			"text" => $entity->getStateMessage()
		));

		$this->add("status", $status);

		$this->createAdd("content","HTMLLabel",array(
			"text"=> mb_strimwidth(strip_tags($entity->getContent()),0,45,"...")
		));

		$displayOrder = null;
		if(method_exists($entity,'getDisplayOrder')){
			$displayOrder = $entity->getDisplayOrder();
		}

		DisplayPlugin::hide("no_label");

		//ラベル表示部
		$this->createAdd("label","LabelList",array(
			"list" => $this->labelList,
			"entryLabelIds"=>$entity->getLabels(),
		));

	}
}

