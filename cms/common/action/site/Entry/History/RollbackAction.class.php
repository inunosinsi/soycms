<?php
/**
 * 記事のロールバックを行います
 * @init entryId
 */
class RollbackAction extends SOY2Action {

	private $entryId;

	public function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		try{
			SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic")->rollback($this->entryId, $form->historyId);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			error_log(var_export($e,true));
			$this->setErrorMessage("failed",CMSMessageManager::get("SOYCMS_ERROR"));
			return SOY2Action::FAILED;
		}

	}
}

class RollbackActionForm extends SOY2ActionForm {
	public $historyId;

	function setHistoryId($historyId){
		$this->historyId = $historyId;
	}
}
