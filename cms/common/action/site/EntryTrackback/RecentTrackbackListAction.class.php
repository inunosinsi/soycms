<?php

class RecentTrackbackListAction extends SOY2Action{

	private $labelId;
	private $limit = 3;
	
	function setlabelId($labelId){
		$this->labelId = $labelId;
	}
	
	function setLimit($limit){
		$this->limit = $limit;
	}
	
	
    function execute() {
    	
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic");
    	$this->setAttribute("trackbacks",$logic->getByLabelIds(array($this->labelId),$this->limit,0));
    	
    	return SOY2Action::SUCCESS;
    
    }
    
    
}
?>