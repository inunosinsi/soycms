<?php

class CreatePage extends CMSWebPageBase {

	var $pageId;

	function doPost(){
		
		if(isset($_POST["next_submit"])){
			
			$html = $script = "";
			
	    	if(soy2_check_token()){
				$action = SOY2ActionFactory::createInstance("Block.CreateAction");
				$result = $action->run();
				
				if($result->success()){
					
					$id = $result->getAttribute("insertedId");
					
					$webPage = SOY2HTMLFactory::createInstance("Block.BlockListPage",array(
			    		"pageId" => $this->pageId
			     	));
			     	
			     	//BlockListPage is a component
			     	$webPage->execute();
			     	$html = $webPage->getObject();
					
					$script = "location.href = '".SOY2PageController::createLink("Block.Detail.".$id)."';";
			
				}
			}
			
			header("Content-Type: text/html; charset=utf-8;");
			echo "<html><head>";
			echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
			echo "</head><body>";
			echo '<div id="result" style="display:none;">'.$html.'</div>';
			echo "<script type=\"text/javascript\">";
			echo 'window.parent.document.main_form.soy2_token.value="'.soy2_get_token().'";';
			echo 'window.parent.document.getElementById("block_list").innerHTML = document.getElementById("result").innerHTML;';
			echo $script;
			echo "</script>";
			echo "</body></html>";			
			exit;
			
		}
	}

    function __construct($args) {
    	$pageId = $args[0];
    	$this->pageId = $pageId;
    	$soyId = $args[1];
    	
    	parent::__construct();
    	
    	$action = SOY2ActionFactory::createInstance("Block.SelectAction");
    	$result = $action->run();
    	//$pageId = $result->getAttribute("pageId");
    	//$soyId = $result->getAttribute("soyId");
    	$blockList = $result->getAttribute("blockList");
    	
    	$this->createAdd("soyId","HTMLInput",array(
			"value"=>$soyId
		));
		
		$this->createAdd("block_id","HTMLLabel",array(
			"text"=>$soyId
		));
		
		$this->createAdd("pageId","HTMLInput",array(
			"value"=>$pageId
		));
    	
    	$this->createAdd("component_loop","BlockList",array(
    		"list"=>$blockList
    	));
    	
    	$this->createAdd("create_form","HTMLForm");
    }
}

class BlockList extends HTMLList{
	
	function populateItem($entity){
		$this->createAdd("component_check","HTMLCheckBox",array(
			"name"=>"class",
			"type"=>"radio",
			"value"=>get_class($entity),
			"label"=>$entity->getComponentName()
		));
		$this->createAdd("component_description","HTMLLabel",array(
			"html"=>$entity->getComponentDescription()
		));
	}	
}


?>