<?php

class BlockListPage extends CMSWebPageBase{

	private $pageId;


	function setPageId($id){
		$this->pageId = $id;
	}

	function execute(){

		$id = $this->pageId;

		$blockList = $this->getBlockList($id);

		DisplayPlugin::toggle("has_uncofigured_block_list", count($blockList["unSetUpBlocks"]));
		$this->createAdd("uncofigured_block_list","BlockListPage_UnSetUpBlockList",array(
			"pageId"=>$id,
			"list" => $blockList["unSetUpBlocks"],
		));

		DisplayPlugin::toggle("has_cofigured_block_list", count($blockList["Blocks"]));
		$this->createAdd("cofigured_block_list","BlockListPage_BlockList",array(
			"list" => $blockList["Blocks"]
		));

		DisplayPlugin::toggle("has_removed_block", count($blockList["removedBlocks"]));
		$this->createAdd("removed_block","BlockListPage_RemovedList",array(
			"list"=>$blockList["removedBlocks"]
		));


		parent::execute();
	}

	function getBlockList($id){
		/*
		 * ブロックのリストを返す
		 * テンプレートに書かれていて、追加されていないもの→unSetUpBlocks
		 * それ以外→Blocks
		 * というキーでリストを返す
		 *
		 * array---unSetUpBlocks---array
		 *	   |
		 *	   --Blocks-----array
		 */
		$action = SOY2ActionFactory::createInstance("Block.TemplateAction",array("pageId"=>$id));
		$result = $action->run();
		$setupedBlocks = $result->getAttribute("setupedBlocks");
		$unsetSoyIds = $result->getAttribute("unsetSoyIds");
		$removedBlocks = $result->getAttribute("removedBlocks");

		return array(
			"unSetUpBlocks"=>$unsetSoyIds,
			"Blocks"=>$setupedBlocks,
			"removedBlocks"=>$removedBlocks
		);
	}
}

class BlockListPage_UnSetUpBlockList extends HTMLList{
	private $pageId;
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	function populateItem($entity){
		$this->addLabel("block_name", array(
			"text"=>$entity
		));

		$this->addLink("block_add_link", array(
			"link" => SOY2PageController::createLink("Block.Create") ."/".$this->pageId . "/" .$entity,
			"id" => "block_" . $entity,
			"onclick" => "return common_click_to_layer(this,{width:800,height:600,header:'ブロックの追加（block:id = ".$entity."）'});"
		));
	}
}

class BlockListPage_BlockList extends HTMLList{

	function populateItem($entity){
		$component = $entity->getBlockComponent();
		$this->addLabel("block_name", array("text"=>$entity->getSoyId()));
		$this->addLabel("block_type", array("text"=>$component->getComponentName()));
		$this->addLabel("block_info", array(
			"id" => "block_info_" . $entity->getId(),
			"text"=>$component->getInfoPage())
		);
		$this->addLink("block_detail_link", array(
			"link"=>SOY2PageController::createLink("Block.Detail.".$entity->getId()),
			"id" => "block_" . $entity->getSoyId(),
			"onclick" => "return common_click_to_layer(this,{width:800,height:650,header:'ブロックの設定（block:id = ".$entity->getSoyId()."）'});"
		));
		$this->addActionLink("block_delete_link", array(
			"link"=>SOY2PageController::createLink("Block.Remove.".$entity->getId()),
			"id" => "block_remove_link_".$entity->getId(),
		));
	}
}

class BlockListPage_RemovedList extends HTMLList{
	function populateItem($entity){
		$component = $entity->getBlockComponent();
		$this->addLabel("block_name", array("text"=>$entity->getSoyId()));
		$this->addLabel("block_type", array("text"=>$component->getComponentName()));
		$this->addLabel("block_info", array("text"=>$component->getInfoPage()));
		$this->addLink("block_detail_link", array(
			"link"=>SOY2PageController::createLink("Block.Detail.".$entity->getId()),
			"id" => "block_" . $entity->getSoyId(),
			"onclick" => "return common_click_to_layer(this,{width:800,height:650,header:'ブロックの設定（block:id = ".$entity->getSoyId()."）'});"
		));
		$this->addActionLink("block_delete_link", array(
			"link"=>SOY2PageController::createLink("Block.Remove.".$entity->getId()),
			"id" => "block_remove_link_".$entity->getId(),
		));
	}
}
