<?php
SOY2::import("action.site.Entry.EntryActionForm");
/**
 * エントリーの更新を行います
 */
class UpdateAction extends SOY2Action{

	/**
	 * エントリーID
	 */
	private $id;

	function setId($id){
		$this->id = $id;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if($form->hasError()){

			$error = "";

			foreach($form as $key => $value){
				if($form->isError($key)){
					$error .= $form->getErrorString($key);
				}
			}

			$this->setErrorMessage("failed",$error);

			return SOY2Action::FAILED;
		}

		$dao = SOY2DAOFactory::create("cms.EntryDAO");
		$logic = SOY2LogicContainer::get("logic.site.Entry.EntryLogic");
		$historyLogic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");

		//記事管理者のためのラベルチェック
		if(!UserInfoUtil::hasSiteAdminRole()){
			try{
				$entry = $logic->getById($this->id);
				$entryLabelIds = $entry->getLabels();
			}catch(Exception $e){
				$this->setErrorMessage('failed','エントリー情報の取得に失敗しました。');
				return SOY2Action::FAILED;
			}

			$prohibitedLabelIds = SOY2LogicContainer::get("logic.site.Label.LabelLogic")->getProhibitedLabelIds();
			//１つでも公開可能でないラベルが付いていたらこの記事を更新できない
			if( count($prohibitedLabelIds) && count($entryLabelIds) && count(array_intersect($prohibitedLabelIds, $entryLabelIds))){
				$this->setErrorMessage('failed','許可されていない操作です。');
				return SOY2Action::FAILED;
			}
		}

		$entity = SOY2::cast("Entry",$form);
		$entity->setCdate(strtotime($form->cdate));

		//無限遠時刻、無限近時刻を設定
		$entity->setOpenPeriodEnd(CMSUtil::encodeDate($entity->getOpenPeriodEnd(),false));
		$entity->setOpenPeriodStart(CMSUtil::encodeDate($entity->getOpenPeriodStart(),true));
		$entity->setId($this->id);
		
		try{
			$logic->update($entity);
			
			//CMS:PLUGIN callEventFunction
			CMSPlugin::callEventFunc('onEntryUpdate',array("entry"=>$entity));
			
			//history
			$historyLogic->onUpdate($entity);

		}catch(Exception $e){
			error_log(var_export($e,true));
			$this->setErrorMessage("failed","Failed to Update Entry");
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;

    }

    function getActionFormName(){
    	return "EntryActionForm";
    }
}
