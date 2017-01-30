<?php

class ImportOrderInfoLogic extends ExImportLogicBase{
	
	private $type;
	
	function __construct(){
		$this->setCharset("Shift_JIS");
	}
	
	function execute(){
		
	}
	
	function setType($type){
		$this->type = $type;
	}
}
?>