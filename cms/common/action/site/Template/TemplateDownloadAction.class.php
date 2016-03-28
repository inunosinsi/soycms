<?php

class TemplateDownloadAction extends SOY2Action{

	private $id;

    function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response) {
    	if(is_null($this->id)){
			return SOY2Action::FAILED;
			exit;	
		}else{
			$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
			$template = $logic->getById($this->id);
			
			if(file_exists($template->getArchieveFileName())){
				$fname = basename($template->getArchieveFileName());
				
				header('Content-Disposition: attachment;filename='.$fname.';');
				echo file_get_contents($template->getArchieveFileName());
				
			}else{
				//404
				header("HTTP/1.0 404 Not Found");
			}
			exit;
		}
    }

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
}
?>