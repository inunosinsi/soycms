<?php

class RecentLabelListAction extends SOY2Action{

    function execute() {
    	$limit = 4;//とりあえず４件表示

    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
    	$logic->setLimit($limit);
    	$result = $logic->getRecentLabelIds();

		//記事管理者の場合
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole()){
			$labelLogic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
			$prohibitedLabelIds = $labelLogic->getProhibitedLabelIds();
			foreach($result as $key => $labelId){
				if(in_array($labelId, $prohibitedLabelIds)){
					unset($result[$key]);
				}
			}
		}

    	$this->setAttribute("list",$result);

    	return SOY2Action::SUCCESS;
    }
}
?>