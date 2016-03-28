<?php

class RecentCommentListAction extends SOY2Action{

    var $limit = 3;
    var $labelId;
	
	function setLimit($limit){
		$this->limit = $limit;
	}
	
	function setLabelId($labelId){
		$this->labelId = $labelId;
	}

    function execute() {
    	
    	//最新エントリーを3件取得
    	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	$dao->setLimit($this->limit);
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
    	$array = $logic->getComments(array($this->labelId),$this->limit,0);
    	$this->setAttribute("comments",$array);
    	
    	return SOY2Action::SUCCESS;
    }
}
?>