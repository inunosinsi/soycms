<?php

class RecentPageListAction extends SOY2Action{

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$dao->setLimit(3);
    	$this->setAttribute("list",$dao->getRecentPages());
    	
    	return SOY2Action::SUCCESS;
    }
}
?>