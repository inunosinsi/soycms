<?php
/**
 * ラベルを複数指定し、そのラベルに割り当てられているエントリーについているすべてのラベルを取得する
 */
class NarrowLabelListAction extends SOY2Action{

	private $labelIds = array();

	function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
	}

	function execute() {

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$subLabels = $logic->getNarrowLabels($this->labelIds);

		//記事管理者の場合
		if(count($subLabels) && !UserInfoUtil::hasSiteAdminRole()){
			$prohibitedLabelIds = $logic->getProhibitedLabelIds();
			if(count($prohibitedLabelIds)){
				foreach($subLabels as $key => $labelId){
					if(in_array($labelId, $prohibitedLabelIds)){
						unset($subLabels[$key]);
					}
				}
			}
		}

		$this->setAttribute("labels",$subLabels);

		return SOY2Action::SUCCESS;

	}
}
?>