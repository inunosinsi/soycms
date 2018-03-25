<?php

/**
 * エントリーの一覧を取得
 * @attribute Entities
 * @attribute total
 */
class EntryListAction extends SOY2Action{

	/**
	 * ラベルID
	 */
	private $id = null;

	/**
	 * ラベルID（複数指定）
	 */
	private $ids = array();

	private $offset;
	private $limit;

	function setId($id){
		$this->id = $id;
	}
	function setIds($ids){
		$this->ids = $ids;
	}
	function setOffset($offset) {
    	$this->offset = $offset;
    }
    function setLimit($limit) {
    	$this->limit = $limit;
    }

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic", array("offset" => $this->offset, "limit" => $this->limit));
		
		try{
			if(!is_null($this->id)){
				//ラベルIDに対するエントリーオブジェクトのリストを返す
				$entries = $logic->getByLabelId($this->id);
				$this->setAttribute("Entities",$entries);

			}else if(is_array($this->ids) && count($this->ids) > 0){
				//ラベルIDを複数指定した場合
				$entries = $logic->getByLabelIds($this->ids);
				$this->setAttribute("Entities",$entries);

			}else{
				//エントリーオブジェクトの配列を返す
				$entries = $logic->getRecentEntries();
				$this->setAttribute("Entities",$entries);
			}

			//合計件数を返す
			$this->setAttribute("total",$logic->getTotalCount());
		}catch(Exception $e){
			$this->setErrorMessage('failed','エントリー一覧の取得に失敗しました');
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
    }
}
