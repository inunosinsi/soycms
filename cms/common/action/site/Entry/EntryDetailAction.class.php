<?php
/**
 * エントリーの詳細情報を取得
 * @attribute Entry
 */
class EntryDetailAction extends SOY2Action{

	/**
	 * エントリーID
	 */
	private $id = null;

	/**
	 * 公開期間を必要とするかどうか
	 * flag = trueなら無限遠をnullに書きかえる
	 *
	 */
	private $flag = true;


	function setId($id){
		$this->id = $id;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		try{
			$entry = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getById($this->id,$this->flag);
		}catch(Exception $e){
			$this->setErrorMessage('failed','エントリー情報の取得に失敗しました。');
			return SOY2Action::FAILED;
		}

		//記事管理者のためのラベルチェック
		if(!UserInfoUtil::hasSiteAdminRole()){
			$entryLabelIds = $entry->getLabels();
			$prohibitedLabelIds = SOY2LogicContainer::get("logic.site.Label.LabelLogic")->getProhibitedLabelIds();
			//１つでも公開可能でないラベルが付いていたらこの記事を更新できない
			if( count($prohibitedLabelIds) && count($entryLabelIds) && count(array_intersect($prohibitedLabelIds, $entryLabelIds))){
				$this->setErrorMessage('failed','許可されていない操作です。');
				return SOY2Action::FAILED;
			}
		}

		$this->setAttribute("Entry",$entry);
		return SOY2Action::SUCCESS;
	}

	function getFlag() {
		return $this->flag;
	}
	function setFlag($flag) {
		$this->flag = $flag;
	}
}
