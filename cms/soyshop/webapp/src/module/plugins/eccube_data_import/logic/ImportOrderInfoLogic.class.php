<?php

class ImportOrderInfoLogic extends ExImportLogicBase{
	
	private $type;
	
	function ImportOrderInfoLogic(){
		$this->setCharset("Shift_JIS");
	}
	
	function execute(){
		
	}
	
	function setType($type){
		$this->type = $type;
	}
}
?>