<?php

class OutlinePage extends CMSWebpageBase{

    function __construct($arg) {
    	$id = @$arg[0];
    	if(is_null($id)){
    		return;
    	}
    	
    	WebPage::__construct();
    	
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

    	$this->createAdd("label_list","HTMLLabel",array("text"=>implode(", ",$this->getLabelNamesFromIds($entry->getLabels()))));
    	$this->createAdd("contents","HTMLLabel",array(
			"html"=>$entry->getContent(),
			"visible" => (boolean) strlen($entry->getContent())
		));
    	$this->createAdd("more","HTMLLabel",array(
			"html"=>$entry->getMore(),
			"visible" => (boolean) strlen($entry->getMore())
		));
    	
    }
    
    function getEntryInformation($id){
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
    
    function getLabelNamesFromIds($labelIds){
		static $labels = null;
		if($labels == null){
			$dao = SOY2DAOFactory::create("cms.LabelDAO");
			$labels = $dao->get();
		}
		$return = array();		
		foreach($labelIds as $key => $value){
			$tmp = $labels[$value];
			$return[] = $tmp->getCaption();
		}
		
		return $return;
	}
}
?>