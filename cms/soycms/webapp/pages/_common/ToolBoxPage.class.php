<?php

class ToolBoxPage extends CMSHTMLPageBase{

    function ToolBoxPage() {
    	HTMLPage::HTMLPage();
    }
    
    function execute(){
    	
    	$enableFileTree = CMSToolBox::isEnableFileTree();
    	
    	/*
    	if($enableFileTree){
    		CMSToolBox::addLink("ファイルを表示",SOY2PageController::createLink("FileManager.File"),true);	
    	} 
    	*/   	
    	
    	$links = CMSToolBox::getLinks();
    	
    	$linkHtml = "";
    	foreach($links as $link){
    		$href = htmlspecialchars($link["link"],ENT_QUOTES,"UTF-8");
    		$onclick = (strlen($link["onclick"])>0) ? " onclick=\"{$link['onclick']}\"" : "" ;
    		$text = htmlspecialchars($link["text"],ENT_QUOTES,"UTF-8");
    		
    		$linkHtml .= "<p><a href=\"{$href}\"{$onclick}>{$text}</a></p>";
    	}
    	$htmls = CMSToolBox::getHTMLs();
    	foreach($htmls as $html){
    		$linkHtml .= "<div>".$html."</div>";    		
    	}
    	
    	$this->createAdd("toolbox_linkbox","HTMLLabel",array(
    		"html" => $linkHtml    	
    	));
    	    	
    	$enableFileTree = false;
   	
		$this->createAdd("singletab","HTMLModel",array(
			"visible" => !$enableFileTree
		));
		
		$this->createAdd("toolbox_tabs","HTMLModel",array(
			"visible" => $enableFileTree
		));
		
		$this->createAdd("toolbox_top","HTMLModel",array(
			"visible" => $enableFileTree
		));
		
		$this->createAdd("toolbox_filetree","HTMLModel",array(
			"visible" => $enableFileTree
		));
    	
    	$this->createAdd("filetree","HTMLLabel",array(
			"manager" => SOY2PageController::createLink("FileManager.File")
		));
    }
}
?>