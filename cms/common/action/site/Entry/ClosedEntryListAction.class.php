<?php

/**
 * エントリーの一覧を取得
 * @attribute Entities
 * @attribute total
 */
class ClosedEntryListAction extends SOY2Action{

	private $offset;
	private $limit;


	function setOffset($offset) {
    	$this->offset = $offset;
    }
    function setLimit($limit) {
    	$this->limit = $limit;
    }

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic", array("offset" => $this->offset, "limit" => $this->limit));
		
		try{
			$list = $logic->getClosedEntryList();
			$this->setAttribute("Entities",$list);
			//合計件数を返す
			$this->setAttribute("total",$logic->getTotalCount());
		}catch(Exception $e){
			$this->setErrorMessage('failed',CMSMessageManager::get("SOYCMS_FAILED_TO_GET_ENTRY_LIST"));
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
    }
}
