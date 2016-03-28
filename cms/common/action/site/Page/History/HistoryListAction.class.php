<?php
/**
 * テンプレートの履歴を取得します
 * @init pageId
 * @attribute historyList
 */
class HistoryListAction extends SOY2Action {

	private $pageId;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		$list = $logic->getHistoryList($this->pageId);
		$this->setAttribute("historyList",$list);
		return SOY2Action::SUCCESS;
    }
}
?>