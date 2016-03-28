<?php

class BlogListAction extends SOY2Action{

    function execute() {
    	$logic = SOY2Logic::createInstance("logic.site.Page.BlogPageLogic");
    	$list = $logic->get();
    	$this->setAttribute("list",$list);
    	return SOY2Action::SUCCESS;
    }
}
?>