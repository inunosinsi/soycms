<?php

class ModifyPopupPage extends CMSWebPageBase{

	private $pageId;
	private $treeId;

	function doPost(){
    	if(soy2_check_token()){
			$this->run("Page.Mobile.MobileUpdateAction",array("pageId"=>$this->pageId,"treeId"=>$this->treeId));
    	}

		$this->printReload();
	}

	function __construct($arg) {
    	$pageId = @$arg[0];
    	$treeId = @$arg[1];

    	$this->pageId = $pageId;
    	$this->treeId = $treeId;

    	parent::__construct();

    	$this->createAdd("main_form","HTMLForm");

    	if(!$this->createForm($pageId,$treeId)){
    		$this->printReload();
    	}

    	$this->createAdd("prototypejs","HTMLLabel",array(
			"html" => HTMLHead::getScriptHTML()
		));

		$this->createAdd("entry_form","HTMLScript",array(
			"script"=>'var entry_form_address="'.SOY2PageController::createLink("Page.Preview.Entry").'?jumpTo='.$pageId.'_'.$treeId.'";'
		));
    }

    function printReload(){
    	echo '<script type="text/JavaScript">';
		echo 'window.parent.document.getElementById("virtual_tree").contentWindow.location.reload();';
		echo 'window.parent.document.main_form.soy2_token.value = \''.soy2_get_token().'\';';
		echo 'window.parent.common_close_layer(window.parent);';
		echo '</script>';
		exit;
    }

    function createForm($pageId,$treeId){

    	if(is_null($pageId)){
    		return false;
    	}
    	$result = $this->run("Page.Mobile.GetMobileDetailPageAction",array("id"=>$pageId));

    	if(!$result->success()){
    		return false;
    	}

    	$page = $result->getAttribute("Page");
    	$trees = $page->getVirtual_tree();

    	//エイリアスリストを出力
		$alias_list = array_map(function($v) { return (string)$v->getAlias(); }, $trees);

    	$this->createAdd("alias_list","HTMLScript",array(
    		"script"=>'var alias_list='.json_encode($alias_list).';'.
    				'var treeId = '.$treeId.';'
    	));

    	if(!isset($trees[$treeId])){
    		return false;
    	}

    	$tree = $trees[$treeId];

    	if(isset($_GET["createdId"])){
    		//新規追加から戻ってきた。
    		$tree->addEntry(@intval($_GET["createdId"]));//直接代入して大丈夫かな？
    	}

    	$this->createAdd("display_title","HTMLInput",array(
    		"name"=>"title",
    		"value"=>$tree->getTitle()
    	));

    	$type_array = array(
    		VirtualTreePage::TYPE_ENTRY => $this->getMessage("SOYCMS_ENTRY"),
    		VirtualtreePage::TYPE_LABEL => $this->getMessage("SOYCMS_LABEL"),
    	);

    	$this->createAdd("display_type","HTMLSelect",array(
    		"options"=>$type_array,
    		"indexOrder"=>true,
    		"selected"=>$tree->getType(),
    		"name"=>"type"
    	));

    	$this->createAdd("display_number","HTMLInput",array(
    		"name"=>"size",
    		"value"=>$tree->getSize()
    	));

    	$this->createAdd("display_alias","HTMLInput",array(
    		"name"=>"alias",
    		"value"=>($treeId != $tree->getAlias()) ? $tree->getAlias() : ""
    	));

    	$this->createAdd("entry_select_component","MobileEntrySelectComponent",array(
			"entries" => $tree->getEntries(),
			"style"=>($tree->getType() == 1) ? "display:none" : ""
		));

		$this->createAdd("label_select_component","MobileLabelSelectComponent",array(
			"labelId"=>$tree->getLabel(),
			"style"=>($tree->getType() != 1) ? "display:none" : ""
		));


		return true;
    }
}

class MobileEntrySelectComponent extends CMSHTMLPageBase{

	private $entries;

	function MobileEntrySelectComponent(){
		HTMLPage::HTMLPage();

	}

	function execute(){
		$this->createAdd("selector_js","HTMLScript",array(
			"lang"=>"text/javascript",
			"script"=>file_get_contents(SOY2::RootDir() . "../soycms/js/entry_selector.js")
		));

		$allEntry = $this->getAllEntry();
		$allEntryIds = array_map(function($v) { return $v["id"]; }, $allEntry);


		$entryIds = array();//array_map(create_function('$v','return (int)$v;'),$this->entity->getEntryId());

		foreach($this->entries as $key => $entry){
			if(!in_array($entry,$allEntryIds)){
				continue;
			}else{
				$entryIds[] = array(
					"order"=>(int)$key,
					"id"=>(int)$entry
				);
			}
		}

		$this->createAdd("entry_list","HTMLScript",array(
			"lang"=>"text/javascript",
			"script"=>'var entryList='.json_encode($allEntry).";\n"
					 .'var initEntries = '.json_encode($entryIds).';'
					 .'var outlineLink = "'.SOY2PageController::createLink("Entry.Outline").'";'
		));

		$this->createAdd("labelList","HTMLSelect",array(
			"options"=>$this->getLabelCaptions(),
			"indexOrder"=>true,
			"property" => "caption"
		));


	}


	function getAllEntry(){
		$dao = SOY2DAOFactory::create("cms.EntryDAO");
    	$array = $dao->get();
    	$ret_val = array();


    	foreach($array as $key => $value){
    		$ret_val[$key] = array();
    		$ret_val[$key]["title"] = htmlspecialchars($value->getTitle());
    		$ret_val[$key]["id"] = $value->getId();
    		$ret_val[$key]["label"] = $this->getLabelIdsFromEntryId($value->getId());
    	}

    	return $ret_val;
	}

	function getLabelIdsByEntryId($entryId){
		static $dao = null;
		if($dao == null){
			$dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		}
		return $dao->getByEntryId($entryId);
	}

	function getLabelIdsFromEntryId($entryId){
		static $labels = null;
		if($labels == null){
			$dao = SOY2DAOFactory::create("cms.LabelDAO");
			$labels = $dao->get();
		}
		$labelIds = $this->getLabelIdsByEntryid($entryId);
		$return = array();
		foreach($labelIds as $key => $value){
			if(isset($labels[$key])){
				$tmp = $labels[$key];
				$return[] = $tmp->getId();
			}
		}

		return $return;
	}

	function getLabelCaptions(){
		try{
			$dao = SOY2DAOFactory::create("cms.LabelDAO");
			$array = $dao->get();
		}catch(Exception $e){
			$array = array();
		}
		return $array;
	}

	function getTemplateFilePath(){

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"){
		   return dirname(__FILE__) . "/ModityPopupEntrySelectForm.html";
		}else{
		   return SOYCMS_LANGUAGE_DIR .SOYCMS_LANGUAGE."/Page/Mobile/ModityPopupEntrySelectForm.html";

		}

	}


	function getEntries() {
		return $this->entries;
	}
	function setEntries($entries) {
		$this->entries = $entries;
	}
}

class MobileLabelSelectComponent extends CMSHTMLPageBase{

	private $labelId;

	function LabeledBlockComponent_FormPage(){
		HTMLPage::HTMLPage();

	}

	function execute(){
		$this->createAdd("label_loop","LabelList",array(
			"list"=>$this->getLabelList(),
			"currentLabel"=>$this->labelId
		));


		$this->createAdd("main_submit","HTMLInput",array(
			"value"=>$this->getMessage("SOYCMS_UPDATE")
		));

	}

   /**
     *  ラベルオブジェクトのリストを返す
     *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
     */
    function getLabelList(){
    	$dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $dao->get();
    }

	function getTemplateFilePath(){

	    if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"){
		   return dirname(__FILE__) . "/ModifyPopupLabelSelectForm.html";
		}else{
		   return SOYCMS_LANGUAGE_DIR .SOYCMS_LANGUAGE."/Page/Mobile/ModifyPopupLabelSelectForm.html";

		}
	}


	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}
}

class LabelList extends HTMLList{
	private $currentLabel;

	function populateItem($entity){

		$this->createAdd("label_radio","HTMLCheckBox",array(
			"value"=>$entity->getId(),
			"name"=>"object[labelId]",
			"selected"=>((string)$this->currentLabel == (string)$entity->getId()),
			"label"=>$entity->getCaption()
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src"=>$entity->getIconUrl()
		));
	}

	function getCurrentLabel() {
		return $this->currentLabel;
	}
	function setCurrentLabel($currentLabel) {
		$this->currentLabel = $currentLabel;
	}
}

?>
