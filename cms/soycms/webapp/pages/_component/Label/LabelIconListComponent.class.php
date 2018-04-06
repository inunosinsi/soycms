<?php

class LabelIconListComponent extends HTMLList{

	function populateItem($entity){
		if(strpos($_SERVER["REQUEST_URI"], "/Page/Detail") === 0){
			$script = "javascript:setChangeLabelIcon('".$entity->filename."','".$entity->url."');";
		}else{
			if(strpos($_SERVER["REQUEST_URI"], "/Label")){
				if(strpos($_SERVER["REQUEST_URI"], "/Detail")){
					$script = "javascript:postChangeLabelIcon(this,'".$entity->filename."');";
				}else{
					$script = "javascript:postChangeLabelIcon('".$entity->filename."');";
				}
			}

		}

		$this->addImage("image_list_icon", array(
			"src" => $entity->url,
			"ondblclick" => $script
		));
	}
}
