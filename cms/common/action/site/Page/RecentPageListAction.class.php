<?php

class RecentPageListAction extends SOY2Action{

	private $limit = 3;

	public function setLimit($limit){
		$this->limit = $limit;
	}

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$dao->setLimit($this->limit);
    	$this->setAttribute("list",$dao->getRecentPages());

    	return SOY2Action::SUCCESS;
    }
}
?>