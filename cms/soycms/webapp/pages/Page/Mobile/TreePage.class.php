<?php

class TreePage extends CMSWebPageBase{

    function __construct($args) {
    	
    	$this->pageId = @$args[0];
    	
    	if(is_null($this->pageId)){
    		//$this->jump("Page");
    	}
    	
    	$result = $this->run("Page.Mobile.GetMobileDetailPageAction",array("id"=>$this->pageId));

    	if(!$result->success()){
    		$this->jump("Page");
    	}
    	$page = $result->getAttribute("Page");
    	parent::__construct();
    	$tree = $this->buildTree($page->getVirtual_tree());
    	
    	$this->createAdd("page_tree","HTMLLabel",array(
    		"html"=>$tree
    	));
    	
    }
    
    function buildTree($virtualTree,$root = 0){
    	$current = $virtualTree[$root];
    	$html = array();
    	
    	$title = $current->getTitle();
    	if(strlen($title) == 0) $title = "<i>".$this->getMessage("SOYCMS_NO_TITLE_2")."</i>";
    	$html[] = $title;
    	
    	$html[] = '<div class="tree_function">';
		
		$html[] = '<a onclick="return window.parent.common_click_to_layer(this,{width:800,height:600})" href="'.SOY2PageController::createLink("Page.Mobile.ModifyPopup").'/'.$this->pageId.'/'.$root.'">[*]</a>';
			
    	if($root != 0){
    		//ルートじゃなかったら
    		$html[] = '<a onclick="if(confirm(\''.$this->getMessage("SOYCMS_CONFIRM_DELETE").'\')){return window.parent.common_click_to_layer(this);}else{return false;}" href="'.SOY2PageController::createLink("Page.Mobile.Delete").'/'.$this->pageId.'/'.$root.'?soy2_token='.soy2_get_token().'">[-]</a>';
			$html[] = '<a onclick="return window.parent.common_click_to_layer(this)" href="'.SOY2PageController::createLink("Page.Mobile.MoveUp").'/'.$this->pageId.'/'.$root.'?soy2_token='.soy2_get_token().'">[↑]</a>';
			$html[] = '<a onclick="return window.parent.common_click_to_layer(this)" href="'.SOY2PageController::createLink("Page.Mobile.MoveDown").'/'.$this->pageId.'/'.$root.'?soy2_token='.soy2_get_token().'">[↓]</a>';
    	}
    	$html[] = '<a onclick="return window.parent.common_click_to_layer(this,{width:800,height:600})" href="'.SOY2PageController::createLink("Page.Mobile.AddAfter").'/'.$this->pageId.'/'.$root.'?soy2_token='.soy2_get_token().'">[+]</a>';
			

		$html[] = '</div>';
    	
    	if(count($current->getChild()) != 0){
	    	$html[] = '<ul  class="virtual_page_tree">';
	    	foreach($current->getChild() as $childId){
	    		$child = @$virtualTree[$childId];
	    		if(is_null($child)) continue;
	    		
	    		$html[] = '<li>'.$this->buildTree($virtualTree,$childId).'</li>';
	    	}
	    	$html[] = '</ul>';
    	}
    	return implode("\n",$html);
    }
}
?>