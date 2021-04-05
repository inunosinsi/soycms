<?php
SOY2::import("base.CMSEntryEditorPageBase");

class DetailPage extends CMSEntryEditorPageBase{

	var $id;
	var $initLabelList;
	var $entryTemplateList;

	function doPost(){

		if($this->id && !isset($_POST["as_new"])){
			//更新
			$result = SOY2ActionFactory::createInstance("Entry.UpdateAction",array(
				"id" => $this->id
			))->run();

			if(!$result->success()){
				$this->addErrorMessage("ENTRY_UPDATE_FAILED");
			}else{
				//ラベル付け
				$label_res = $this->run("EntryLabel.EntryLabelUpdateAction",array(
					"id" => $this->id
				));

				if(!$label_res->success()){
					$this->addErrorMessage("LABEL_STICK_FAILED");
				}else{
					$this->addMessage("ENTRY_UPDATE_SUCCESS");
				}
			}
		}else{
			//作成
			$result = SOY2ActionFactory::createInstance("Entry.CreateAction")->run();

			if(!$result->success()){
				$this->addErrorMessage("ENTRY_CREATE_FAILED");
			}else{
				$this->id = $result->getAttribute("id");

				//ラベル付け
				$label_res = $this->run("EntryLabel.EntryLabelUpdateAction",array(
					"id" => $this->id
				));

				if(!$label_res->success()){
					$this->addErrorMessage("LABEL_STICK_FAILED");
				}else{
					$this->addMessage("ENTRY_CREATE_SUCCESS");
				}
			}
		}

		$this->jump("Entry.Detail.".$this->id);
	}




	function setInitLabelList($initLabelList){
		$this->initLabelList = $initLabelList;
	}

	function __construct($arg) {

		//$id == null ならば新規作成
		$this->id = (isset($arg[0])) ? (int)$arg[0] : null;

		parent::__construct();
	}

	function main(){

		$backList = (isset($_COOKIE["Entry_List"]))?	".".$_COOKIE["Entry_List"] :	"";

		if(!preg_match('/^[0-9\.]*$/i',$backList)){
			$backList = "";
		}
		$this->addLink("back_entry_list", array(
			"link"=>SOY2PageController::createLink("Entry.List".$backList)
		));

		$this->addLabel("page_title", array(
			"text" => ($this->id) ? CMSMessageManager::get("SOYCMS_ENTRY_DETAIL") : CMSMessageManager::get("SOYCMS_CREATE_NEW")
		));


		//WYSIWYG設定 CMSEntryEditorPageBase#setupWYSIWYG
		$this->setupWYSIWYG($this->id, $this->initLabelList, false);

		//フォーム設定
		$this->setupForm();

		//記事新規作成ページへのリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ADD_NEW_ENTRY"),SOY2PageController::createLink("Entry.Detail"));

		//メモを編集のリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_EDIT_MEMO"),"javascript:void(0);",false,"edit_entry_memo();");

		//記事履歴のリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ENTRY_HISTORY"),SOY2PageController::createLink("Entry.History.".$this->id),true);

		//ラベル管理へのリンク(内部で書き換え可能にする)
		if(UserInfoUtil::hasSiteAdminRole()){
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_LABEL_MANAGER"),SOY2PageController::createLink("Label"));
		}

		//ラベルの追加
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ADD_NEW_LABEL"),"javascript:void(0);",false,"create_label();");

		//雛形へのリンク
		if(CMSUtil::isEntryTemplateEnabled()){
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ENTRY_TEMPLATE"),SOY2PageController::createLink("EntryTemplate"));
		}

		//記事雛形適用
		$toolBoxForSelectTemplte = "";
		$toolBoxForSelectTemplte .= '<div class="form-group input-group">';
		//$toolBoxForSelectTemplte .= '<span class="input-group-addon">雛形を読み込む<span soy:message="HELP_ENTRY_TEMPLATE"></span></span>';
		$toolBoxForSelectTemplte .= '<select id="list_templates" class="form-control"><option value="">---</option>';
		foreach($this->getEntryTemplateList() as $templateId => $templateObj){
			$toolBoxForSelectTemplte .= '<option value="'.htmlspecialchars($templateId,ENT_QUOTES,"UTF-8").'">'.htmlspecialchars($templateObj->getName(),ENT_QUOTES,"UTF-8").'</option>';
		}
		$toolBoxForSelectTemplte .= '</select>';
		$toolBoxForSelectTemplte .= '<span class="input-group-btn">';
		$toolBoxForSelectTemplte .= '<input type="button" value="雛形を読み込む" onclick="if($(\'#list_templates\').val().length >0 && confirm(\'編集内容は破棄されますが雛形を読み込みますか？\')){applyTemplate()}" class="btn btn-default">';
		$toolBoxForSelectTemplte .= '</span>';
		$toolBoxForSelectTemplte .= '</div>';
		CMSToolBox::addHTML($toolBoxForSelectTemplte);


		//ページジャンプセレクターを追加
		CMSToolBox::enableFileTree();
		CMSToolBox::addPageJumpBox();

	}

	/**
	 * ラベルオブジェクトの配列を返す
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
	 * 記事オブジェクトを返します
	 * @param $id nullだったら空の記事
	 */
	function getEntryInformation($id){
		if(is_null($id)){
			$entry = SOY2DAOFactory::create("cms.Entry");
			if(!empty($this->initLabelList)){
				$labelId = $this->initLabelList[0];

				foreach($this->getEntryTemplateList() as $entryTemplate){
					if($entryTemplate->getLabelId() == $labelId){
						$templates = $entryTemplate->getTemplates();
						$entry->setContent($templates["content"]);
						$entry->setMore($templates["more"]);
						$entry->setStyle($templates["style"]);
						break;
					}
				}
			}


			return $entry;
		}

		$action = SOY2ActionFactory::createInstance("Entry.EntryDetailAction",array("id"=>$id));
		$result = $action->run();
		if($result->success()){
			return $result->getAttribute("Entry");
		}else{
			return new Entry();
		}

	}

	function getCSSList(){

		$result = $this->run("CSS.ListAction");
		if(!$result->success()){
			return array();
		}else{
			$list = $result->getAttribute("list");

			//リストの整形
			$list = array_map(function($v) {return array( "id" => $v->getId(),"filePath" => $v->getFilePath()); }, $list);

			return $list;
		}
	}

	function getEntryTemplateList(){
		if(is_null($this->entryTemplateList)){
			$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateListAction")->run();
			$this->entryTemplateList = $result->getAttribute("list");
		}

		return $this->entryTemplateList;
	}

	function getEntryCSSList(){
		$result = $this->run("EntryTemplate.EntryCSSAction");
		return $result->getAttribute("EntryCSS");

	}

	/**
	 * フォームの構築
	 */
	function setupForm(){

		$id = $this->id;

		//記事情報をフォームに格納
		$entry = $this->getEntryInformation($id);

		$this->addInput("title", array(
			"value"=>$entry->getTitle(),
			"name"=>"title"
		));

		$this->addTextArea("content", array(
			"value"=>$entry->getContent(),
			"name"=>"content",
			"class"=>self::getEditorClass(),
			"rows" => max(3,count(explode("\n",$entry->getContent())))
		));


		$this->addTextArea("more", array(
			"value"=>$entry->getMore(),
			"name"=>"more",
			"class"=>self::getEditorClass(),
			"rows" => max(3,count(explode("\n",$entry->getContent()))),
		));

		$this->addInput("style", array(
			"value"=>$entry->getStyle(),
			"name"=>"style",
		));

		$this->createAdd("state_draft","HTMLCheckBox",array(
			"name" =>"isPublished",
			"value" => "0",
			"type"=>"radio",
			"label" =>  CMSMessageManager::get("SOYCMS_DRAFT"),
			"selected"=>!$entry->getIsPublished()
		));

		$this->createAdd("state_public","HTMLCheckBox",array(
			"name"=>"isPublished",
			"value"=>"1",
			"type"=>"radio",
			"label" => CMSMessageManager::get("SOYCMS_PUBLISHED"),
			"selected"=>$entry->getIsPublished()
		));

		$this->createAdd("publish_info","HTMLLabel",array(
			"text"=>($entry->getIsPublished()) ? CMSMessageManager::get("SOYCMS_STAY_PUBLISHED") : CMSMessageManager::get("SOYCMS_DRAFT")
		));


		$this->createAdd("createdate","HTMLInput",array(
			"name" =>"cdate",
			"value" => date('Y-m-d H:i:s',$entry->getCdate())
		));

		$this->createAdd("createdate_show","HTMLLabel",array(
			"text" => date('Y-m-d H:i:s',$entry->getCdate())
		));

		$this->createAdd("updatedate_show","HTMLLabel",array(
			"text" => strlen($entry->getUdate()) ? date('Y-m-d H:i:s',$entry->getUdate()) : "-",
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

		$this->createAdd("period_info","HTMLLabel",array("html"=>$open_period_text));

		//公開期間フォームここまで

		$this->createAdd("update_button","HTMLInput",array(
			"value" => ($this->id) ? CMSMessageManager::get("SOYCMS_UPDATE") : CMSMessageManager::get("SOYCMS_CREATE"),
			"name"=>"modify",
			"type"=>"submit",
			"disabled" => ((boolean)!UserInfoUtil::hasEntryPublisherRole() && $entry->getIsPublished())
		));

		$this->createAdd("create_button","HTMLInput",array(
			"visible" => false,//(boolean)$this->id, 新規保存は廃止
			"type"	=>"submit",
			"name"	=> "as_new",
			"value"   =>CMSMessageManager::get("SOYCMS_SAVE_AS_A_NEW_ENTRY"),
			"onclick" => ((boolean)UserInfoUtil::hasEntryPublisherRole()) ? 'return confirm_open();' : "",
		));

		$this->createAdd("label_categories", "CategorizedLabelList", array(
			"list" => $this->getCategorizedLabelList(),
			"selectedLabelList" => $this->initLabelList ? $this->initLabelList : $entry->getLabels(),
		));

		//フォーム
		$this->addForm("detail_form",array());


		//記事雛形
		$this->addModel("has_entry_template",array(
			"visible" => is_array($this->getEntryTemplateList()) && count($this->getEntryTemplateList()),
		));
		$this->createAdd("list_templates","HTMLSelect",array(
			"name"=>"template",
			"options"=> $this->getEntryTemplateList(),
			"property"=>"name",
			"indexOrder"=>true
		));

		//記事ラベルのメモ
		$this->createAdd("entry_label_memos","EntryLabelMemoList",array(
			"selectedLabelList" => $entry->getLabels(),
			"list" =>  $this->getLabelList()
		));

		//記事のメモ
		$this->createAdd("description","HTMLInput",array(
				"value"=>$entry->getDescription()
		));
		$this->createAdd("entry_memo_wrapper","HTMLModel",array(
			"style" => strlen($entry->getDescription()) ? "" : "display:none;"
		));
		$this->createAdd("entry_memo_input","HTMLInput",array(
				"value" => $entry->getDescription(),
				"readonly" => true,
		));

	}
}

class CategorizedLabelList extends HTMLList{
	private $selectedLabelList = array();

	public function populateItem($entity, $key){
		$this->addLabel("category_name",array(
			"text" => $key,
		));
		$this->createAdd("labels", "LabelList", array(
			"list" => $entity,
			"selectedLabelList" => $this->selectedLabelList
		));
	}

	public function setSelectedLabelList($array){
		if(is_array($array)){
			$this->selectedLabelList = $array;
		}
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

	public function populateItem($entity){

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
			"text" => $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";",
			"title" => $entity->getBranchName(),
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"alt" => $entity->getBranchName(),
			"title" => $entity->getBranchName(),
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

	public function populateItem($entity){
		$this->addLabel("entry_label_memo",array(
				"id" => "entry_label_memo_".$entity->getId(),
				"text" => $entity->getCaption(),
				"title" => $entity->getDescription(),
				"style"=> ( in_array($entity->getId(),$this->selectedLabelList) ? "" : "display:none;" )."color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";"
		));
	}

}
