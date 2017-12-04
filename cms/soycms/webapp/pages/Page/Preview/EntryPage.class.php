<?php
SOY2::import("base.CMSEntryEditorPageBase");

class EntryPage extends CMSEntryEditorPageBase{

	private $id;

	private $jumpTo;

	function doPost(){
		if(soy2_check_token()){
			if($this->id && !isset($_POST["as_new"])){
				$result = SOY2ActionFactory::createInstance("Entry.UpdateAction",array(
					"id" => $this->id
				))->run();
			}else{
				$result = SOY2ActionFactory::createInstance("Entry.CreateAction")->run();
				$this->id = $result->getAttribute("id");
			}

			//ラベル付け
			$result = SOY2ActionFactory::createInstance("EntryLabel.EntryLabelUpdateAction",array(
				"id" => $this->id
			))->run();
			if(!is_null($this->jumpTo)){
				//BlockとかMobileVirtualTreeから呼ばれたときの動作
				if(strpos($this->jumpTo,"_") === false){
					//Block
					$this->jump("Block.Detail.".$this->jumpTo."?createdId=".$this->id);
				}else{
					list($pageId,$treeId) = explode("_",$this->jumpTo);
					$redirect = SOY2PageController::createLink("Page.Mobile.ModifyPopup.".$pageId.".".$treeId."?createdId=".$this->id);
					echo '<html><head><script lang="text/javascript">';
					echo 'if(window.parent) window.parent.document.main_form.soy2_token.value=\''.soy2_get_token().'\';';
					echo 'location.href=\''.$redirect.'\';';
					echo '</script></head></html>';
				}
				exit;

			}else{
				echo '<html><head><script lang="text/javascript">window.parent.location.reload();</script></head></html>';
			}
		}
		exit;
	}

	function __construct($arg) {

		//$id == null ならば新規作成
		$this->id = (isset($arg[0]) && is_numeric($arg[0])) ? $arg[0] : null;
		$this->jumpTo = (isset($_GET["jumpTo"])) ? $_GET["jumpTo"] : null;

		parent::__construct();

		//フォーム設定
		$this->setupForm();

		//WYSIWYG設定 CMSEntryEditorPageBase#setupWYSIWYG
		$this->setupWYSIWYG($this->id,null,false);

		//ラベル管理へのリンク(内部で書き換え可能にする)
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_LABEL_MANAGER"),SOY2PageController::createLink("Label"));

		//雛形へのリンク
		if(CMSUtil::isEntryTemplateEnabled()){
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ENTRY_TEMPLATE"),SOY2PageController::createLink("EntryTemplate"));
		}

		//メモを編集のリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_EDIT_MEMO"),"javascript:void(0);",false,"edit_entry_memo();");

		//ラベルの追加
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ADD_NEW_LABEL"),"javascript:void(0);",false,"create_label();");

	}

	/**
	 * ラベルオブジェクトの配列を返す
	 */
	private function getLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.LabelListAction");
		$result = $action->run();

		if($result->success()){
			return $result->getAttribute("list");
		}else{
			return array();
		}
	}

	/**
	 * 記事オブジェクトを返します
	 * @param $id nullだったら空の記事
	 */
	private function getEntryInformation($id){
		if(is_null($id)){
			return SOY2DAOFactory::create("cms.Entry");
		}

		$action = SOY2ActionFactory::createInstance("Entry.EntryDetailAction",array("id"=>$id));
		$result = $action->run();
		if($result->success()){
			return $result->getAttribute("Entry");
		}else{
			return new Entry();
		}

	}

	private function getCSSList(){

		$result = $this->run("CSS.ListAction");
		if(!$result->success()){
			return array();
		}else{
			$list = $result->getAttribute("list");

			//リストの整形
			$list = array_map(function($v) {return array("id"=>$v->getId(),"filePath"=>$v->getFilePath());}, $list);

			return $list;
		}
	}

	private function getEntryTemplateList(){
		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateListAction")->run();
		return $result->getAttribute("list");
	}

	private function getEntryCSSList(){
		$result = $this->run("EntryTemplate.EntryCSSAction");
		return $result->getAttribute("EntryCSS");
	}

	private function setupForm(){

		$id = $this->id;

		//記事情報をフォームに格納
		$entry = $this->getEntryInformation($id);

		$this->createAdd("title","HTMLInput",array(
			"value"=>$entry->getTitle(),
			"name"=>"title"
		));

		$this->createAdd("content","HTMLTextArea",array(
			"value"=>$entry->getContent(),
			"name"=>"content",
			"class"=>"mceEditor"
		));

		$this->createAdd("more","HTMLTextArea",array(
			"value"=>$entry->getMore(),
			"name"=>"more",
			"class"=>"mceEditor"
		));

		$this->createAdd("style","HTMLInput",array(
			"value"=>$entry->getStyle(),
			"name"=>"style",
		));

		$this->createAdd("state_draft","HTMLCheckBox",array(
			"name" =>"isPublished",
			"value" => "0",
			"type"=>"radio",
			"label" => CMSMessageManager::get("SOYCMS_DRAFT"),
			"selected"=>!$entry->getIsPublished()
		));


		$this->createAdd("createdate","HTMLInput",array(
			"name" =>"cdate",
			"value" => date('Y-m-d H:i:s',$entry->getCdate())
		));

		$this->createAdd("createdate_show","HTMLLabel",array(
			"text" => date('Y-m-d H:i:s',$entry->getCdate())
		));

		$this->createAdd("state_public","HTMLCheckBox",array(
			"name"=>"isPublished",
			"value"=>"1",
			"type"=>"radio",
			"label" => CMSMessageManager::get("SOYCMS_PUBLISHED"),
			"selected"=>$entry->getIsPublished()
		));

		//記事管理者用
		$this->createAdd("publish_info","HTMLLabel",array(
			"text"=>($entry->getIsPublished()) ? CMSMessageManager::get("SOYCMS_STAY_PUBLISHED") : CMSMessageManager::get("SOYCMS_DRAFT")
		));

		$this->createAdd("description","HTMLInput",array(
			"value"=>$entry->getDescription()
		));

		$start = $entry->getOpenPeriodStart();
		$end   = $entry->getOpenPeriodEnd();


		//公開期間フォームの表示
		$this->createAdd("start_date","HTMLInput",array(
			"value"=>(is_null($start)) ? "" : date('Y-m-d H:i:s',$start),
			"name"=>"openPeriodStart"
		));
		$this->createAdd("end_date","HTMLInput",array(
			"value"=>(is_null($end)) ? "" : date('Y-m-d H:i:s',$end),
			"name"=>"openPeriodEnd"
		));

		$open_period_text = CMSUtil::getOpenPeriodMessage($start, $end);
		$this->createAdd("open_period_show","HTMLLabel",array(
			"html" => $open_period_text
		));

		//記事管理者用
		$this->createAdd("period_info","HTMLLabel",array("html"=>$open_period_text));


		//公開期間フォームここまで

		$this->createAdd("update_button","HTMLInput",array(
			"value" => ($this->id) ? CMSMessageManager::get("SOYCMS_UPDATE")  : CMSMessageManager::get("SOYCMS_CREATE"),
			"name"=>"modify",
			"type"=>"submit",
			"disabled" => ((boolean)!UserInfoUtil::hasEntryPublisherRole() && $entry->getIsPublished())
		));

		$this->createAdd("create_button","HTMLInput",array(
			"visible" => ((boolean)$this->id || is_null($this->id)),
			"type"=>"submit",
			"name" => "as_new",
			"value"=>CMSMessageManager::get("SOYCMS_SAVE_AS_A_NEW_ENTRY"),
			"onclick"=> ((boolean)!UserInfoUtil::hasEntryPublisherRole()) ? 'return confirm_open();' : ""
		));

		$list = SOY2HTMLFactory::createInstance("LabelList",array(
			"includeParentTag" => true
		));
		//記事に選択されているラベルIDを全て渡す
		$labels = $this->getLabelList();

		$list->setSelectedLabelList($entry->getLabels());
		$list->setList($labels);
		$this->add("labels",$list);


		//フォーム
		$action = SOY2PageController::createLink("Page.Preview.Entry");
		if(!is_null($id)){
			$action .= "/".$id;
		}

		if(!is_null($this->jumpTo)){
			$action .= "?jumpTo=".$this->jumpTo;
		}

		$this->addForm("detail_form",array(
			 "action"=>$action
		));

		$this->createAdd("list_templates","HTMLSelect",array(
			"name"=>"template",
			"options"=> $this->getEntryTemplateList(),
			"property"=>"name",
			"indexOrder"=>true
		));

		$this->addModel("ajax_url",array(
			"script" => 'var templateAjaxURL = "'.SOY2PageController::createLink("EntryTemplate.GetTemplateAjaxPage").'";'
		));

		//記事ラベルのメモ
		$this->createAdd("entry_label_memos","EntryLabelMemoList",array(
			"selectedLabelList" => $entry->getLabels(),
			"list" =>  $labels
		));

		//記事のメモ
		$this->createAdd("description","HTMLInput",array(
				"value"=>$entry->getDescription()
		));
		$this->createAdd("entry_memo_wrapper","HTMLModel",array(
				//"style" => strlen($entry->getDescription()) ? "" : "display:none;"
		));
		$this->createAdd("entry_memo_input","HTMLInput",array(
				"value" => $entry->getDescription(),
				"readonly" => true,
		));


		//ボタンの出しわけ:更新ボタン
		$this->createAdd("show_update_button", "HTMLModel", array(
			"visible" => (is_numeric($this->id) && (int)$this->id > 0)
		));

		//ボタンの出しわけ:新規作成ボタン
		$this->createAdd("show_create_button", "HTMLModel", array(
			"visible" => (is_null($this->id))
		));
	}
}

class LabelList extends HTMLList{

	private $selectedLabelList = array();

	public function setIncludeParentTag($inc){
		$this->setAttribute("includeParentTag",$inc);
	}

	public function setSelectedLabelList($array){
		if(is_array($array)){
			$this->selectedLabelList = $array;
		}
	}

	protected function populateItem($entity){
		$elementID = "label_".$entity->getId();

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"	  => "label[]",
			"value"	 => $entity->getId(),
			"selected"  => in_array($entity->getId(),$this->selectedLabelList),
			"elementId" => $elementID,
			"onclick" => 'toggle_labelmemo(this.value,this.checked);'
		));
		$this->createAdd("label_label","HTMLModel",array(
			"for" => $elementID,
		));
		$this->createAdd("label_caption","HTMLLabel",array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";"
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));
	}

}

class EntryLabelMemoList extends HTMLList{

	private $selectedLabelList = array();

	public function setSelectedLabelList($array){
		if(is_array($array)){
			$this->selectedLabelList = $array;
		}
	}

	protected function populateItem($entity){
		$this->addLabel("entry_label_memo",array(
				"id" => "entry_label_memo_".$entity->getId(),
				"text" => $entity->getCaption(),
				"title" => $entity->getDescription(),
				"style"=> ( in_array($entity->getId(),$this->selectedLabelList) ? "" : "display:none;" )."color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";"
		));
	}

}
