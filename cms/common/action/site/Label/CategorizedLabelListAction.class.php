<?php
/**
 * 分類されたラベル一覧の取得
 * @attribute list
 */
class CategorizedLabelListAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$useLabelCategory = UserInfoUtil::getSiteConfig("useLabelCategory");
		try{
			$labels = $logic->getWithAccessControl();

			$categorizedLabelLists = $classifiedLabels = array();
			foreach($labels as $label){
				if($label->isEditableByNormalUser()){
					if($useLabelCategory){
						$categorizedLabelLists[$label->getCategoryName()][] = $label;
					}else{
						$categorizedLabelLists[""][] = $label;
					}
				}else{
					$classifiedLabels[] = $label;
				}
			}

			//カテゴリー名でソート
			ksort($categorizedLabelLists);

			//最後に*の付いたラベル
			if(count($classifiedLabels)){
				$categorizedLabelLists["記事管理者には表示されないラベル"] = $classifiedLabels;
			}

			$this->setAttribute("list",$categorizedLabelLists);
		}catch(Exception $e){
			$this->setErrorMessage("failed","ラベル一覧の取得失敗");
		}
		return SOY2Action::SUCCESS;
	}
}
?>