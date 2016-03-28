<?php

class CustomPageAction extends SOY2Action{

    function execute($request,$form,$response) {
    	$dao = SOY2DAOFactory::create("cms.PluginDAO");
    	$plugin = $dao->getByid($form->id);
    	$customArr = $plugin->getCustom();
    	
    	if(!isset($customArr[$form->menuId])){
    		return SOY2Action::FAILED;
    	}
    	$menu = $customArr[$form->menuId];
    	
    	$html = call_user_func($menu["func"]);
    	
    	$this->setAttribute("html",$html);
    	return SOY2Action::SUCCESS;
    	
    }
}

class CustomPageActionForm extends SOY2ActionForm{
	var $id;
	var $menuId;
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getMenuId() {
		return $this->menuId;
	}
	function setMenuId($menuId) {
		$this->menuId = $menuId;
	}
}

?>