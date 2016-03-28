<?php
/**
 * エントリーの表示順を変更します
 */
class UpdateDisplayOrderAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$displayOrders = $form->displayOrder;
		$logic = SOY2LogicContainer::get("logic.site.Entry.EntryLogic");

		$labelLogic = SOY2LogicContainer::get("logic.site.Label.LabelLogic");
		$prohibitedLabelIds = $labelLogic->getProhibitedLabelIds();

		try{
			foreach($displayOrders as $entryId => $tmp){
				foreach($tmp as $labelId => $displayOrder){

					//アクセスできないラベルに関する操作はできない
					if(count($prohibitedLabelIds) && in_array($labelId, $prohibitedLabelIds)){
						continue;
					}

					if(!strlen($displayOrder))$displayOrder = LabeledEntry::ORDER_MAX;
					$logic->updateDisplayOrder($entryId,$labelId,$displayOrder);
				}
			}
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setErrorMessage("failed","表示順変更に失敗しました");
			return SOY2Action::FAILED;
		}
    }
}

class UpdateDisplayOrderActionForm extends SOY2ActionForm{
	var $displayOrder;

	/**
	 * @validator number {"require":"true"}
	 */
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}
}
?>