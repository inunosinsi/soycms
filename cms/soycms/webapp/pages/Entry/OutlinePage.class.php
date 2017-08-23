<?php

class OutlinePage extends CMSWebpageBase{

	function __construct($arg) {
		$id = @$arg[0];
		if(is_null($id)){
			return;
		}

		parent::__construct();

		$entry = $this->getEntryInformation($id);

		$this->createAdd("title","HTMLLabel",array("text"=>$entry->getTitle()));
		$this->createAdd("entry_state","HTMLLabel",array("text"=>$entry->getStateMessage()));
		$this->createAdd("cdate","HTMLLabel",array("text"=>date("Y-m-d H:i:s",$entry->getCdate())));
		$this->createAdd("udate","HTMLLabel",array("text"=>date("Y-m-d H:i:s",$entry->getUdate())));

		$this->createAdd("open_period","HTMLModel",array(
			"visible"=> !( is_null(CMSUtil::decodeDate($entry->getOpenPeriodStart())) && is_null(CMSUtil::decodeDate($entry->getOpenPeriodEnd())) )
		));
		$this->createAdd("open_period_start","HTMLLabel",array(
			"visible"=> ! is_null(CMSUtil::decodeDate($entry->getOpenPeriodStart())),
			"text"=> date("Y-m-d H:i:s", CMSUtil::decodeDate($entry->getOpenPeriodStart()))
		));
		$this->createAdd("open_period_end","HTMLLabel",array(
			"visible"=> ! is_null(CMSUtil::decodeDate($entry->getOpenPeriodEnd())),
			"text"=> date("Y-m-d H:i:s", CMSUtil::decodeDate($entry->getOpenPeriodEnd()))
		));

		$this->createAdd("contents","HTMLLabel",array(
			"html"=>$entry->getContent(),
			"visible" => (boolean) strlen($entry->getContent())
		));
		$this->createAdd("more","HTMLLabel",array(
			"html"=>$entry->getMore(),
			"visible" => (boolean) strlen($entry->getMore())
		));

		//ラベル
		$this->createAdd("entry_label_list","EntryLabelMemoList",array(
				"selectedLabelList" => $entry->getLabels(),
				"list" =>  $this->getLabelList()
		));

	}

	private function getEntryInformation($id){
		if(is_null($id)){
			return SOY2DAOFactory::create("cms.Entry");
		}

		$action = SOY2ActionFactory::createInstance("Entry.EntryDetailAction",array("id"=>$id,"flag"=>false));
		$result = $action->run();
		if($result->success()){
			return $result->getAttribute("Entry");
		}else{
			return new Entry();
		}

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

}

/**
 *
 * Entry.DetailのEntryLabelMemoListと同じ
 *
 */
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

