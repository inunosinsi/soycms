<?php
/**
 * ラベル一覧の取得
 * @attribute list
 */
class LabelListAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		try{
			$labels = SOY2Logic::createInstance("logic.site.Label.LabelLogic")->getWithAccessControl();
			$this->setAttribute("list", $labels);
		}catch(Exception $e){
			$this->setErrorMessage("failed", "ラベル一覧の取得失敗");
		}
		return SOY2Action::SUCCESS;
	}
}
