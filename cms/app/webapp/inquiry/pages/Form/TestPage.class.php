<?php

class TestPage extends WebPage{

    function TestPage() {
    
    	$formDAO = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$forms = $formDAO->get();
    	
    	$columnDAO = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    	
    	$templates = array();
    	$columnList = array();
    	$oldSiteId = "";
    	
    	$counter = 0;
    	
    	echo "<pre>";
    	foreach($forms as $form){
    		
    		$type = substr(strstr($form->getFormId(),"_"),1);
    		$siteId = substr($form->getFormId(),0,strpos($form->getFormId(),"_"));
    		    		
    		if(count($templates)<4){
    			$templates[$type] = $dir = SOY2::RootDir() . "template/" . $form->getFormId();
    			$columns = $columnDAO->getByFormId($form->getId());
    			$columnList[$type] = $columns;
    			$oldSiteId = $siteId; 			
    			continue;
    		}
    		
    		
    		//テンプレートのコピー
			$old = $templates[$type]; 
			$dir = SOY2::RootDir() . "template/" . $form->getFormId();
			if(!file_exists($dir))mkdir($dir);
			
			$columns = $columnDAO->getByFormId($form->getId());
			$oldColumns = $columnList[$type];
			
			$files = scandir($old);
			foreach($files as $file){
				if($file[0] == ".")continue;
				$filepath = $dir . "/" . $file;
				
				$content = file_get_contents($old . "/" . $file);
				
				foreach($oldColumns as $key => $column){
					$content = str_replace("[".$column->getId()."]", "[".$columns[$key]->getId()."]",$content);
					$content = str_replace("/".$oldSiteId,"/".$siteId,$content);
				}
				
				echo $filepath . "\n";
				
				foreach($columns as $column){
					echo $column->getId() . "\n";
				}
				echo "-------------------------------------\n";
				
				
				echo htmlspecialchars($content);
				echo "<hr>";
				file_put_contents($filepath,$content);
			}
			    		
    	}
    	echo "</pre>";
    
    	
    	exit;
    }
}
?>