<?php
SOY2::import("action.site.Entry.EntryActionForm");

/**
 * エントリーを作成する
 * @attribute id
 */
class CreateAction extends SOY2Action{

	const ERROR_CODE_01 = 1;

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if($form->hasError()){
			foreach($form as $key => $value){
				$this->setErrorMessage($key,$form->getErrorString($key));
			}
			return SOY2Action::FAILED;
		}

		$dao = SOY2DAOFactory::create("cms.EntryDAO");
		$logic = SOY2LogicContainer::get("logic.site.Entry.EntryLogic");
		$historyLogic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");

		$entity = SOY2::cast("Entry",$form);
		$entity->setCdate(strtotime($form->cdate));

		//無限遠時刻、無限近時刻を設定
		$entity->setOpenPeriodEnd(CMSUtil::encodeDate($entity->getOpenPeriodEnd(),false));
		$entity->setOpenPeriodStart(CMSUtil::encodeDate($entity->getOpenPeriodStart(),true));

		try{
			$id = $logic->create($entity);
			$entity->setId($id);	//for onEntryCreate

			//CMS:PLUGIN callEventFunction
			$this->setAttribute("id",$id);
			CMSPlugin::callEventFunc('onEntryCreate',array("entry"=>$entity));

			//history
			$historyLogic->onCreate($entity);

		}catch(Exception $e){
			return SOY2Action::FAILED;
		}



		return SOY2Action::SUCCESS;

    }

    function getActionFormName(){
    	return "EntryActionForm";
    }

}
?>