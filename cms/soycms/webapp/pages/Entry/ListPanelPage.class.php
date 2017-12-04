<?php

class ListPanelPage extends CMSUpdatePageBase{

	public function doPost(){
		switch($_POST['op_code']){
			case "stick":
				$result = $this->run("Label.StickLabelAction");
				break;
			case "unstick":
				$result = $this->run("Label.UnstickLabelAction");
				break;
			case "overwrite":
				$result = $this->run("Label.OverwriteLabelAction");
				break;
			default:
				$result = null;
		}

		if($result->success()){
			echo '<html><head><script lang="text/JavaScript">window.parent.location.reload();</script></head></html>';
		}
		exit;
	}

	function __construct($arg) {
		$labels = array_map(function($v){ return (int)$v; }, $arg);

		parent::__construct();

		$result = $this->run("Label.LabelListAction");
		$this->createAdd("label_list","LabelList",array(
			"list"=>$result->getAttribute("list"),
			"selectedIds"=>$labels
		));

		$this->addForm("main_form");

		//ラベル一覧用のCSS
// 		HTMLHead::addLink("listPanel",array(
// 			"rel" => "stylesheet",
// 			"type" => "text/css",
// 			"href" => SOY2PageController::createRelativeLink("./css/entry/listPanel.css")
// 		));
		HTMLHead::addLink("labelList",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/label/labelList.css")
		));

	}
}

class LabelList extends HTMLList{
	private $selectedIds;

	public function setSelectedIds($ids){
		$this->selectedIds = $ids;
	}

	protected function populateItem($entity){
		$elementID = "label_".$entity->getId();

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"	  => "label[]",
			"value"	 => $entity->getId(),
			"selected"=>in_array($entity->getId(),$this->selectedIds),
			"elementId" => $elementID,
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
