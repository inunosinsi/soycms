<?php

class DeleteAction extends SOY2Action{

    function execute($request,$form,$response) {
    	$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
    	if(!is_array($form->trackback_id)) $form->trackback_id = array();
    	try{
    		foreach($form->trackback_id as $trackbackId){
    			$dao->delete($trackbackId);
    		}
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	return SOY2Action::SUCCESS;
    }
}

class DeleteActionForm extends SOY2ActionForm{
	var $trackback_id;

	function setTrackback_id($trackback_id) {
		$this->trackback_id = $trackback_id;
	}
}
?>