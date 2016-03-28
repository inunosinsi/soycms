<?php

class CreateAction extends SOY2Action{

    function execute($request,$form,$response) {
    	$dao = SOY2DAOFactory::create("cms.CSSDAO");
		$css = new CSS();
		$css->setFilePath($form->path);
		try{
			$dao->insert($css);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
    }
}

class CreateActionForm extends SOY2ActionForm{
	var $path;
	
	function setPath($path){
		$this->path = $path;
	}

}
?>