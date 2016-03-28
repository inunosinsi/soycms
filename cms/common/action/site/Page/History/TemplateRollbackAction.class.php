<?php
/**
 * テンプレートのロールバックを行います
 * @init pageId
 */
class TemplateRollbackAction extends SOY2Action {

	private $pageId;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		$entity = $logic->getById($this->pageId);
		$history = $logic->getHistoryById($form->historyId);
		$entity->setTemplate($history->getContents());
		
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