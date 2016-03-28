<?php

class TrackbackDetailAction extends SOY2Action{

	private $trackbackId;

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
    	
    	try{
    		$trackback = $dao->getById($this->trackbackId);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    	$this->setAttribute("entity",$trackback);
    	
    	return SOY2Action::SUCCESS;
    	
    }

   

    function getTrackbackId() {
    	return $this->trackbackId;
    }
    function setTrackbackId($trackbackId) {
    	$this->trackbackId = $trackbackId;
    }
}
?>