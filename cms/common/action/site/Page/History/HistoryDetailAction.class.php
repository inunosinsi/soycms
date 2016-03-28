<?php
/**
 * テンプレートの履歴を取得します
 * @init pageId
 * @init historyId
 * @attribute TemplateHistory
 */
class HistoryDetailAction extends SOY2Action {

	private $pageId;
	private $historyId;
	
	function setPageId($id){
		$this->pageId = $id;
	}
	function setHistoryId($id){
		$this->historyId = $id;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		try{
			$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
			$history = $logic->getHistoryById($this->historyId);

			if($history->getPageId() != $this->pageId){
				return SOY2Action::FAILED; 
			}
			
			$this->setAttribute("TemplateHistory",$history);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED; 
		}
    }
}
