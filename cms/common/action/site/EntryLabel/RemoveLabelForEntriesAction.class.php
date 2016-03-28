<?php
/**
 * エントリーからラベルをはずします
 */
class  RemoveLabelForEntriesAction extends SOY2Action{

	/**
	 * ラベルのID
	 */
	private $id;

	function setId($id){
		$this->id  = $id;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$labelId = $request->getParameter("label_select");
		$this->setAttribute("label_select",$labelId);

		$id = $labelId;
		$entries = $form->entry;

		if(!is_array($entries)){
			$entries = array();
		}

		//記事管理者のためのラベルチェック
		if(!UserInfoUtil::hasSiteAdminRole()){
			$labelLogic = SOY2LogicContainer::get("logic.site.Label.LabelLogic");
			$prohibitedLabelIds = $labelLogic->getProhibitedLabelIds();
			//アクセスできないラベルを外すことはできない
			if(count($prohibitedLabelIds) && in_array($id, $prohibitedLabelIds)){
				$this->setErrorMessage('failed','許可されていない操作です。');
				return SOY2Action::FAILED;
			}
		}

		//ラベル外し実行
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		try{
			foreach($entries as $key => $entry_id){

				//CMS:PLUGIN callEventFunction
				CMSPlugin::callEventFunc('onEntryLabelRemove',array("entryId"=>$entry_id,"labelId"=>$id));

				$logic->unsetEntryLabel($entry_id,$id);
			}
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setAttribute("failed","エントリーからラベルをはずすのに失敗しました");
			return SOY2Action::FAILED;
		}


	}

}

class RemoveLabelForEntriesActionForm extends SOY2ActionForm{

	var $entry = array();

	/**
	 * @validator Array {"type":"number"}
	 */
	function setEntry($entry){
		$this->entry = $entry;
	}

}
?>
