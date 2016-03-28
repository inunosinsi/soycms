<?php

class UpdateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		SOY2::import("domain.cms.CSS");
		$css = SOY2::cast("CSS",$form);
		$logic = SOY2Logic::createInstance("logic.site.CSS.CSSLogic");
		try{
			$logic->update($css);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
		
	}
}

class UpdateActionForm extends SOY2ActionForm{
	var $id;
	var $filePath;
	var $contents;

	function setId($id) {
		$this->id = $id;
	}
	function setFilePath($filePath) {
		$this->filePath = $filePath;
	}
	function setContents($contents) {
		$this->contents = $contents;
	}
}

?>