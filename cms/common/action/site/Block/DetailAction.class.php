<?php

class DetailAction extends SOY2Action{

	private $id;
	
	function setId($id){
		$this->id = $id;
	}

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.BlockDAO");
    	$this->setAttribute("Block",$dao->getById($this->id));
    	return SOY2Action::SUCCESS;
    }
}
?>