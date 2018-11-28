<?php

class ABM_StartPage extends ABM_PageBase{

	var $message = array();

	function doPost(){

		if(isset($_POST["next"])){

			$pageList = @$_POST["page_list"];
			if(empty($pageList)){
				$this->message[] = "ページが選択されていません。";
			}

			$blockId = @$_POST["block_id"];
			if(strlen($blockId)<1 || !preg_match('/^[a-zA-Z0-9_-]+$/',$blockId)){
				$this->message[] = "ブロックIDが空または不正な値です。";
			}

			$class = @$_POST["class"];

			//値の保存
			$session = $this->getSession();
			$session["page_list"] = $pageList;
			$session["block_id"] = $blockId;
			$session["class"] = $class;

			$this->saveSession($session);

			if(empty($this->message)){
				$this->goNext();
			}
		}

	}

    function __construct(){
    	parent::__construct();

    	$session = $this->getSession();
		$pageList = (isset($session["page_list"])) ? $session["page_list"] : array();
		$blockId = @$session["block_id"];
		$class = @$session["class"];

		if(isset($session["message"])){
			$this->message[] = $session["message"];
			unset($session["message"]);
			$this->saveSession($session);
		}

		//メッセージを表示
    	$this->createAdd("message","HTMLLabel",array(
    		"html" => implode("<br>",$this->message),
    		"visible" => (count($this->message) > 0)
    	));


    	//ページを取得
    	$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
    	$pages = $pageDAO->get();
    	$this->createAdd("page_list","PageList",array(
    		"list"=>$pages,
    		"checkedPageList" => $pageList
    	));

		$this->createAdd("checkall","HTMLCheckBox",array(
			"name"=>"checkall",
			"type"=>"checkbox",
			"value"=>1,
			"onclick"=>"(function(){var form=document.pages;for(var i=0;i<form.length;i++){if(form[i].name=='checkall') continue; if(form[i].type=='checkbox') form[i].checked=!form[i].checked;} return true; })();",
			"label"=>"チェックを反転する",
			"selected" => false
		));

    	//ブロックの種類を出力
    	$blockList = Block::getBlockComponentList();

    	$this->createAdd("component_loop","BlockList",array(
    		"list"=>$blockList,
    		"selectedClassName" => $class
    	));

    	//ブロックIDを出力
    	$this->createAdd("block_id","HTMLInput",array(
    		"name" => "block_id",
    		"value" => $blockId
    	));
    }

    function getTemplateFilePath(){
		return dirname(__FILE__) . "/ABM_StartPage.html";
	}
}

class BlockList extends HTMLList{

	var $selectedClassName = "EntryBlockComponent";

	function setSelectedClassName($className){
		$this->selectedClassName = $className;
	}

	function populateItem($entity){

		$this->createAdd("component_check","HTMLCheckBox",array(
			"name"=>"class",
			"type"=>"radio",
			"value"=>get_class($entity),
			//"onclick"=>"refleshDescription('".$entity->getComponentDescription()."');",
			"label"=>$entity->getComponentName(),
			"selected" => ($this->selectedClassName == get_class($entity))
		));
	}
}

class PageList extends HTMLList{

	var $checkedPageList = array();

	function setCheckedPageList($list){
		$this->checkedPageList = $list;
	}

	function populateItem($entity){

		$this->createAdd("checkbox","HTMLCheckbox",array(
			"name" => "page_list[]",
			"type" => "checkbox",
			"value" => $entity->getId(),
			"selected" => (in_array($entity->getId(),$this->checkedPageList)),
			"label" => $entity->getUri()
		));

		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getTitle()
		));

//		$this->createAdd("url","HTMLLabel",array(
//			"text" => $entity->getUri()
//		));

	}
}
