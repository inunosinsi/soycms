<?php

class ABM_ConfirmPage extends ABM_PageBase{

	function doPost(){

		if(isset($_POST["next"])){

			//実行OK
			$session = $this->getSession();
			$pageList = $session["page_list"];
			$block = $this->createBlock();

			$soyId = $block->getSoyId();
			$blockDAO = SOY2DAOFactory::create("cms.BlockDAO");
			$blockDAO->begin();
			foreach($pageList as $pageId){

				try{
					//updateの場合
					$tmpBlock = $blockDAO->getPageBlock($pageId,$soyId);
					$block->setId($tmpBlock->getId());
					$block->setPageId($pageId);
					$blockDAO->update($block);

				}catch(Exception $e){
					$block->setPageId($pageId);
					$blockDAO->insert($block);
				}
			}
			$blockDAO->commit();

			$this->saveSession(array("message" => "更新しました。"));
			CMSPlugin::redirectConfigPage();
		}

		if(isset($_POST["prev"])){
			$session = $this->getSession();
			unset($session["object"]);
			$this->saveSession();
			$this->goBack();
		}
	}

    function __construct(){
    	parent::__construct();

    	//対象ページを出力
    	$session = $this->getSession();
		$pageList = $session["page_list"];
		$this->createAdd("page_list","PageList",array(
			"list" => $pageList
		));

    	$block = $this->createBlock();
    	$component = $block->getBlockComponent();

    	$this->addLabel("block_name", array(
    		"text" => $component->getComponentName()
    	));

    	$this->addLabel("block_info", array(
    		"text" => $component->getInfoPage()
    	));

    	$this->addLabel("block_id", array(
    		"text" => $block->getSoyId()
    	));

    }
}

class PageList extends HTMLList{

	var $checkedPageList = array();
	var $dao;

	function setCheckedPageList($list){
		$this->checkedPageList = $list;
	}

	function getPage($id){
		if(!$this->dao)$this->dao = SOY2DAOFactory::create("cms.PageDAO");

		try{
			$page = $this->dao->getById($id);
		}catch(Exception $e){
			$page = new Page();
		}

		return $page;
	}

	function populateItem($value){

		$entity = $this->getPage($value);

		$this->createAdd("checkbox","HTMLCheckbox",array(
			"name" => "page_list[]",
			"type" => "checkbox",
			"value" => $entity->getId(),
			"selected" => (in_array($entity->getId(),$this->checkedPageList))
		));

		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getTitle()
		));

		$this->createAdd("url","HTMLLabel",array(
			"text" => $entity->getUri()
		));

	}
}
