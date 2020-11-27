<?php
SOY2::import("base.CMSEntryEditorPageBase");
class EntryPage extends CMSEntryEditorPageBase{

	var $id;
	var $pageId;
	var $detail;

	function doPost(){

		if($this->id && !isset($_POST["as_new"])){
			$result = SOY2ActionFactory::createInstance("Entry.UpdateAction",array(
				"id" => $this->id
			))->run();
			if(!$result->success()){
				$this->addErrorMessage("ENTRY_UPDATE_FAILED");
			}
		}else{
			$result = SOY2ActionFactory::createInstance("Entry.CreateAction")->run();
			$this->id = $result->getAttribute("id");
			if(!$result->success()){
				$this->addErrorMessage("ENTRY_CREATE_FAILED");
			}
		}

		//アクションを呼ぶ前にラベルがあるかどうかチェック
		if(!isset($_POST["label"])){
			$this->addMessage("ENTRY_BLOG_LABEL_NOTFOUND");

			if($this->id){
				$this->jump("Blog.Entry.".$this->pageId."/".$this->id);
			}else{
				$this->jump("Blog.Entry.".$this->pageId);
			}
		}

		if(!$result->success()){
			$this->addErrorMessage("ENTRY_UPDATE_FAILED");
		}else{
			//ラベル付け
			$label_res = $this->run("EntryLabel.EntryLabelUpdateAction",array(
					"id" => $this->id
			));

			if($label_res->success()){
				$this->addMessage("ENTRY_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("LABEL_STICK_FAILED");
			}
		}

		$this->jump("Blog.Entry.".$this->pageId.".".$this->id);
	}

	function __construct($arg) {
		//$id == null ならば新規作成
		$this->pageId = @$arg[0];
		$this->id = @$arg[1];

		parent::__construct();


		$result = $this->run("Blog.DetailAction",array("id"=>$this->pageId));
		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}

		$this->detail = $result->getAttribute("Page");

		//ブログに使用するラベルが設定されていなければ、メッセージを表示して設定ページへリダイレクト
		if(is_null($this->detail->getBlogLabelId())){
			$this->jump("Blog.Config.".$this->pageId);
		}


		//公開側へのリンク
// 		if($this->detail->isActive() == Page::PAGE_ACTIVE && $this->detail->getGenerateTopFlag()){
// 			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_BLOGPAGE"),CMSUtil::getSiteUrl() . $this->detail->getTopPageURL(),false,"this.target = '_blank'");
// 		}

		//メモを編集のリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_EDIT_MEMO"),"javascript:void(0);",false,"edit_entry_memo();");

		//記事履歴のリンク
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ENTRY_HISTORY"),SOY2PageController::createLink("Entry.History.".$this->id),true);

		//ラベル管理へのリンク(内部で書き換え可能にする)
		if(UserInfoUtil::hasSiteAdminRole()){
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_LABEL_MANAGER"),SOY2PageController::createLink("Label"));
		}

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

		//ページジャンプ
		if(UserInfoUtil::hasEntryPublisherRole()){
			CMSToolBox::addPageJumpBox();
		}

		//WYSIWYG設定 CMSEntryEditorPageBase#setupWYSIWYG
		$this->setupWYSIWYG($this->id, $this->detail->getBlogLabelId(),false);

		//フォーム設定
		$this->setupForm();


		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->pageId)
		));

	}

	/**
	 * ラベルオブジェクトの配列を返す
	 */
	function getLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.BlogLabelListAction",array("pageId"=>$this->pageId));
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

	function getCSSList(){

		$result = $this->run("CSS.ListAction");
		if(!$result->success()){
			return array();
		}else{
			$list = $result->getAttribute("list");

			//リストの整形
			$list = array_map(function($v) { return array( "id "=> $v->getId(),"filePath" => $v->getFilePath()); }, $list);

			return $list;
		}
	}

	function getEntryTemplateList(){
		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateListAction")->run();
		return $result->getAttribute("list");
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

		$this->createAdd("title","HTMLInput",array(
			"value"=>$entry->getTitle(),
			"name"=>"title"
		));

		$this->createAdd("content","HTMLTextArea",array(
			"value"=>$entry->getContent(),
			"name"=>"content",
			"class"=>self::getEditorClass()
		));

		$this->createAdd("style","HTMLInput",array(
			"value"=>$entry->getStyle(),
			"name"=>"style",
		));

		$this->createAdd("more","HTMLTextArea",array(
			"value"=>$entry->getMore(),
			"name"=>"more",
			"class"=>self::getEditorClass()
		));

		$this->createAdd("state_draft","HTMLCheckBox",array(
			"selected"=>!$entry->getIsPublished(),
			"name"=>"isPublished",
			"value"=>0,
			"label"=>CMSMessageManager::get("SOYCMS_DRAFT")
		));
		$this->createAdd("state_public","HTMLCheckBox",array(
			"selected"=>$entry->getIsPublished(),
			"name"=>"isPublished",
			"value"=>1,
			"label"=>CMSMessageManager::get("SOYCMS_PUBLISHED")
		));

		$this->createAdd("publish_info","HTMLLabel",array(
			"text"=>($entry->getIsPublished()) ? CMSMessageManager::get("SOYCMS_STAY_PUBLISHED") : CMSMessageManager::get("SOYCMS_DRAFT")
		));

		$this->createAdd("createdate","HTMLInput",array(
			"name" =>"cdate",
			"value" => (is_numeric($entry->getCdate())) ? date('Y-m-d H:i:s',$entry->getCdate()) : ""
		));

		$this->createAdd("createdate_show","HTMLLabel",array(
			"text" => (is_numeric($entry->getCdate())) ? date('Y-m-d H:i:s',$entry->getCdate()) : ""
		));

		$this->createAdd("updatedate_show","HTMLLabel",array(
			"text" => (is_numeric($entry->getUdate())) ? date('Y-m-d H:i:s',$entry->getUdate()) : "-",
		));


		$start = $entry->getOpenPeriodStart();
		$end   = $entry->getOpenPeriodEnd();

		//公開期間フォームの表示
		$this->createAdd("start_date","HTMLInput",array(
			"value"=>(is_numeric($start)) ? date('Y-m-d H:i:s',$start) : "",
			"name"=>"openPeriodStart"
		));
		$this->createAdd("end_date","HTMLInput",array(
			"value"=>(is_numeric($end)) ? date('Y-m-d H:i:s',$end) : "",
			"name"=>"openPeriodEnd"
		));

		$open_period_text = CMSUtil::getOpenPeriodMessage($start, $end);
		$this->createAdd("open_period_show","HTMLLabel",array(
			"html" => $open_period_text
		));

		$this->createAdd("period_info","HTMLLabel",array("html"=>$open_period_text));

		//公開期間フォームここまで


		//新規投稿ボタン
		$this->createAdd("update_button","HTMLInput",array(
			"value" => ($this->id) ? CMSMessageManager::get("SOYCMS_UPDATE") : CMSMessageManager::get("SOYCMS_CREATE"),
			"name" => "modify",
			"onclick"=>'return confirm_trackback();',
			"disabled" => ((boolean)!UserInfoUtil::hasEntryPublisherRole() && $entry->getIsPublished())
		));

		$this->createAdd("create_button","HTMLInput",array(
			"visible" => false,//(boolean)$this->id, 新規保存は廃止
			"type"	=>"submit",
			"name"	=> "as_new",
			"value"   =>CMSMessageManager::get("SOYCMS_SAVE_AS_A_NEW_ENTRY"),
			"onclick" => ((boolean)UserInfoUtil::hasEntryPublisherRole()) ? 'return confirm_open() && confirm_trackback();' : "",
		));

		//ラベル
		//記事に選択されているラベルIDを全て渡す
		//新規作成時はすべてのラベルにチェックを入れる
		$labels = $this->getLabelList();
		$this->createAdd("labels","LabelList",array(
			"selectedLabelList" => $entry->getLabels(),
			"blogLabelId" => $this->detail->getBlogLabelId(),
			"list" => $labels
		));

		//ブログで使わないが設定されていたラベル
		$this->createAdd("hidden_labels","HiddenLabelList",array(
			"list" => is_array($entry->getLabels()) ? array_diff($entry->getLabels(),array_keys($labels)) : array()
		));

		//フォーム
		$this->addForm("detail_form");

		$this->addScript("ajax_url",array(
			"script" => 'var templateAjaxURL = "'.SOY2PageController::createLink("EntryTemplate.GetTemplateAjaxPage").'";'
		));

		//記事ラベルのメモ
		$this->createAdd("entry_label_memos","EntryLabelMemoList",array(
			"selectedLabelList" => $entry->getLabels(),
			"list"			  => $labels,
		));

		//メモ
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

		//記事ページへのリンク
		if($this->detail->isActive() == Page::PAGE_ACTIVE && $this->detail->getGenerateEntryFlag() && $entry->getIsPublished()){
			CMSToolBox::addLink(
				CMSMessageManager::get("SOYCMS_SHOW_ENTRYPAGE"),
				CMSUtil::getSiteUrl() . $this->detail->getEntryPageURL(true).rawurlencode($entry->getAlias()),
				false,
				"this.target = '_blank'"
			);
		}

	}
}

class LabelList extends HTMLList{

	private $selectedLabelList = array();
	private $blogLabelId;

	public function setSelectedLabelList($array){
		if(is_array($array)){
			$this->selectedLabelList = $array;
		}
	}

	public function setBlogLabelId($labelId){
		$this->blogLabelId = $labelId;
	}

	public function populateItem($entity){

		$elementID = "label_".$entity->getId();
		$isBlogLabel = ($entity->getId() == $this->blogLabelId);

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"	  => "label[]",
			"value"	 => $entity->getId(),
			"elementId" => $elementID,
			"selected"  => $isBlogLabel ? "true" : in_array($entity->getId(),$this->selectedLabelList),
			"onclick"   => $isBlogLabel ? 'return false;' : 'toggle_labelmemo(this.value,this.checked);'
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

class HiddenLabelList extends HTMLList{

	public function populateItem($entity){
		$this->createAdd("hidden_label","HTMLInput",array(
			"name"  => "label[]",
			"value" => $entity,
			"type"  => "hidden"
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

		$text = "[".$entity->getCaption()."] ";
		$description = $entity->getDescription();
		if(strlen($description)){
			$text .= "(".$description.") ";
		}else{
			$text .= CMSMessageManager::get("SOYCMS_IS_SET_NOW");
		}

		$this->addLabel("entry_label_memo",array(
				"id" => "entry_label_memo_".$entity->getId(),
				"text" => $entity->getCaption(),
				"title" => $entity->getDescription(),
				"style"=> ( in_array($entity->getId(),$this->selectedLabelList) ? "" : "display:none;" )."color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";"
		));
	}

}
