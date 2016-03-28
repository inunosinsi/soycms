<?php

SOY2::import("base.validator.SOY2ActionFormValidator_ArrayValidator");

/**
 * エントリーにラベルをつけます
 */
class  EntryLabelUpdateAction extends SOY2Action{

	/**
	 * エントリーID
	 */
	private $id;

	function setId($id){
		$this->id  = $id;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		$entryid = $this->id;
		$labels = $form->label;

		if(!is_array($labels)){
			$labels = array();
		}
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		try{
			$currentLabels = $logic->getLabeledEntryByEntryId($entryid);
		}catch(Exception $e){
			$currentLabels = array();
		}

		//記事管理者のためのラベルチェック
		if(!UserInfoUtil::hasSiteAdminRole()){
			//非公開のラベルが設定されている記事は変更できない
			$entryLabelIds = array_keys($currentLabels);
			$labelLogic = SOY2LogicContainer::get("logic.site.Label.LabelLogic");
			$prohibitedLabelIds = $labelLogic->getProhibitedLabelIds();
			//１つでも公開可能でないラベルが付いていたらこの記事を更新できない
			if( count($prohibitedLabelIds) && count($entryLabelIds) && count(array_intersect($prohibitedLabelIds, $entryLabelIds))){
				$this->setErrorMessage('failed','許可されていない操作です。');
				return SOY2Action::FAILED;
			}

			//非公開のラベル設定は変更できない
			foreach($prohibitedLabelIds as $prohibitedLabelId){
				if( !isset($currentLabels[$prohibitedLabelId]) && in_array($prohibitedLabelId, $labels) ){
					//付けようとしているので外す
					foreach($labels as $key => $labelId){
						if($labelId == $prohibitedLabelId){
							unset($labels[$key]);
						}
					}
				}elseif( isset($currentLabels[$prohibitedLabelId]) && !in_array($prohibitedLabelId, $labels) ){
					//外そうとしているので付ける
					$labels[] = $prohibitedLabelId;
				}
			}

		}

		try{

			foreach($labels as $labelId){

				if(isset($currentLabels[$labelId])){
					//現在指定されたラベルがすでに割り当てられている。
					unset($currentLabels[$labelId]);
				}else{
					//CMS:PLUGIN callEventFunction
					CMSPlugin::callEventFunc('onEntryLabelApply',array("entryId"=>$entryid,"labelId"=>$labelId));
					$logic->setEntryLabel($entryid, $labelId);
				}
			}

			//ここに残っているcurrentLabelsは消すべきものたち
			foreach($currentLabels as $labelId => $entryLabel){
				$logic->unsetEntryLabel($entryid, $labelId);
			}

			CMSPlugin::callEventFunc('afterEntryLabelsApply',array("entryId"=>$entryid));


			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setErrorMessage("failed","エントリーのラベル付けに失敗しました。");
			return SOY2Action::FAILED;
		}


	}

}

class EntryLabelUpdateActionForm extends SOY2ActionForm{

	var $label = array();

	/**
	 * @validator Array {"type":"number"}
	 */
	function setLabel($label){
		$this->label = $label;
	}

}
?>
