<?php

class IndexPage extends WebPage{

    function IndexPage($args) {
    	WebPage::WebPage();
    	
    	$dir = SOY2::RootDir() . "template/";
    	$files = scandir($dir);
    	
    	$list = array();
    	foreach($files as $file){
    		if($file[0] == ".")continue;
    		$list[$file] = array();
    		
    		$templates = scandir($dir . "/" . $file);
    		
    		foreach($templates as $template){
    			if($template[0] == ".")continue;
    			$list[$file][] = $template;		
    		}
    	}
    	
    	
    	$this->createAdd("template_list","HTMLLabel",array(
    		"html" => $this->buildTree($list)
    	));
    }
    
    function buildTree($list){
    	
    	$_link = SOY2PageController::createLink(APPLICATION_ID . ".Template.Edit");
    	
    	ob_start();
    	echo "<ul>";
    	foreach($list as $name => $array){
    		echo "<li class=\"tree_container\">";
    		echo $name;
    		
    		echo "<ul class=\"tree\">";
    		foreach($array as $file){
    			
    			$link = $_link . "?target=" . $name . "/" . $file;
    			
    			echo "<li>";
    			echo "<a href=\"$link\">";
    			echo $file;
    			echo "</a>";
    			echo "</li>";	
    			
    		}
    		echo "</ul>";
    		
    		echo "</li>";
    	}
    	echo "</ul>";
    	$html = ob_get_contents();
    	ob_end_clean();
    	
    	
    	return $html;
    }
}
?>