<?php
/**
 * テンプレートのロールバックを行います
 * @init pageId
 */
class TemplateRollbackAction extends SOY2Action {

	private $pageId;
	private $mode;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		$entity = $logic->getById($this->pageId);
		$history = $logic->getHistoryById($form->historyId);
		
		$ent_contents = unserialize($entity->getTemplate());
		$hist_contents = unserialize($history->getContents());
		
		
		$ent_contents[$this->mode] = $hist_contents[$this->mode];
		
		$entity->setTemplate(serialize($ent_contents));
		
		try{
			$logic->update($entity);
		}catch(Exception $e){
			$this->setErrorMessage("failed","ページの更新に失敗しました");
			return SOY2Action::FAILED;
		}
				
		return SOY2Action::SUCCESS;
    }
}

class TemplateRollbackActionForm extends SOY2ActionForm {
	var $historyId;
	
	function setHistoryId($historyId){
		$this->historyId = $historyId;
	}
}
?>