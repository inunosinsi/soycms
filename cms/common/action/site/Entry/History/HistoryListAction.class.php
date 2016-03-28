<?php
/**
 * 記事の履歴を取得します
 * @init entryId
 * @init page
 * @attribute historyList
 */
class HistoryListAction extends SOY2Action {

	private $entryId;
	private $limit = 10;
	private $page = 0;

	public function setEntryId($entryId){
		$this->entryId = $entryId;
	}
	public function setPage($page){
		$this->page = $page;
	}
	public function setLimit($limit){
		$this->limit = $limit;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		$offset = $this->page * $this->limit;

		$logic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");
		$list = $logic->getHistories($this->entryId,$offset,$this->limit);
		$this->setAttribute("historyList",$list);

		$count = $logic->countHistories($this->entryId);
		$this->setAttribute("hasNext",$count > $offset + $this->limit);
		$this->setAttribute("hasPrev",$this->page > 0);

		return SOY2Action::SUCCESS;
	}
}
