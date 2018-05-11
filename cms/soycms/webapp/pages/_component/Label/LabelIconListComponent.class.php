<?php

class LabelIconListComponent extends HTMLList{

	function populateItem($entity){

		$this->addImage("image_list_icon", array(
			"src" => $entity->url,
			"ondblclick" => self::buildScript($entity)
		));
	}

	private function buildScript($entity){
		if(strpos($_SERVER["REQUEST_URI"], "/Page/Detail") !== false || strpos($_SERVER["REQUEST_URI"], "Blog/Config") !== false){
			return "javascript:setChangeLabelIcon('".$entity->filename."','".$entity->url."');";
		}

		if(strpos($_SERVER["REQUEST_URI"], "/Label")){
			if(strpos($_SERVER["REQUEST_URI"], "/Detail")){
				return "javascript:postChangeLabelIcon(this,'".$entity->filename."');";
			}else{
				return "javascript:postChangeLabelIcon('".$entity->filename."');";
			}
		}

		return "";
	}
}
