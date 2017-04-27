<?php

class DetailPage extends CMSWebPageBase{

	var $labelId;
	
	function doPost(){

    	if(soy2_check_token()){
    		$res = $this->run("Label.LabelUpdateAction",array(
	    		"id" => $this->labelId
	    	));
			
			if($res->success()){
				$this->addMessage("LABEL_UPDATE_SUCCESS");
			}else{
				$this->addMessage("LABEL_UPDATE_FAILED");			
			}
			
			$this->jump("Label.Detail.".$this->labelId);
    	}
	}

    function __construct($args) {
    	$labelId = @$args[0];
    	$this->labelId = $labelId;
    	
    	WebPage::__construct();
    	
    	$res = $this->run("Label.LabelDetailAction",array(
    		"id" => $labelId
    	));
    	
    	//無かった場合
    	if(!$res->success()){
    		$this->jump("Label");
    	}
    	
    	$label = $res->getAttribute("label");
    	$this->buildForm($label);
    	
    	//アイコンリスト
    	$this->createAdd("image_list","LabelIconList",array(
    		"list" => $this->getLabelIconList()
    	));

		// colorpickerプラグイン
		HTMLHead::addScript("colorpicker", array(
			"src" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.js"),
		));
		
    	HTMLHead::addLink("colorpicker",array(
    		"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.css"),
    	));
    	
    	$this->createAdd("update_form","HTMLForm");
    }
    
    function buildForm($entity){
    	
    	$this->createAdd("caption","HTMLInput",array(
    		"value" => $entity->getCaption(),
    		"name" => "caption" 
    	));
    	
    	$this->createAdd("alias","HTMLInput",array(
    		"value" => $entity->getAlias(),
    		"name" => "alias" 
    	));    	
    	
    	$this->createAdd("label_icon","HTMLImage",array(
    		"src" => $entity->getIconUrl(),
    		"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
    	));
    	$this->createAdd("icon","HTMLInput",array(
    		"value" => $entity->getIcon(),
    		"name" => "icon",
    		"id" => "labelicon"
    	));
    	
    	$this->createAdd("description","HTMLTextArea",array(
    		"value" => $entity->getDescription(),
    		"name" => "description"
    	));
    	
    	$this->createAdd("color","HTMLInput",array(
    		"value" => sprintf("%06X",$entity->getColor()),
    		"name" => "color"
    	));
    	
    	$this->createAdd("background_color","HTMLInput",array(
    		"value" => sprintf("%06X",$entity->getBackgroundColor()),
    		"name" => "backgroundColor"
    	));
    	
    	$this->createAdd("preview","HTMLLabel",array(
    		"text"=> $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";margin:5px"
    	));
    	
    	
    }
    
    /**
     * ラベルに使えるアイコンの一覧を返す
     */
    function getLabelIconList(){
    	
    	$dir = CMS_LABEL_ICON_DIRECTORY;
    	
    	$files = scandir($dir);
    	
    	$return = array();
    	
    	foreach($files as $file){
    		if($file[0] == ".")continue;
    		
    		$return[] = (object)array(
    			"filename" => $file,
    			"url" => CMS_LABEL_ICON_DIRECTORY_URL . $file,
    		);
    	}
    	
    	
    	return $return;    	
    }
}


class LabelIconList extends HTMLList{
	
	function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:postChangeLabelIcon(this,'".$entity->filename."');"
		));
	}
	
}
?>