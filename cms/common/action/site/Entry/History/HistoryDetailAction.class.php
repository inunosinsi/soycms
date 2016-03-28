<?php
/**
 * 記事の履歴を取得します
 * @init entryId
 * @init historyId
 * @attribute EntryHistory
 */
class HistoryDetailAction extends SOY2Action {

	private $entryId;
	private $historyId;

	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
	function setHistoryId($id){
		$this->historyId = $id;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		try{
			$logic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");
			$history = $logic->getHistory($this->historyId);

			if($history->getEntryId() != $this->entryId){
				return SOY2Action::FAILED;
			}

			$this->setAttribute("EntryHistory",$history);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
	}
}
