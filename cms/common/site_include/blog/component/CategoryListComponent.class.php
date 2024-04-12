<?php

/**
 * カテゴリーを表示
 */
class CategoryListComponent extends HTMLList{

	var $categoryUrl;
	var $entryCount = array();

	protected function populateItem($entry){
		$id = (is_numeric($entry->getId())) ? (int)$entry->getId() : 0;

		if($id > 0){
			$onLoads = CMSPlugin::getEvent("onPageOutputLabelRead");
			if(is_array($onLoads) && count($onLoads)) {
				foreach($onLoads as $plugin){
					$res = call_user_func($plugin[0], array('labelId' => $id));
					if(is_numeric($res) && $res > 0 && $res !== $id){
						$id = (int)$res;
						$entry = soycms_get_label_object($id);
					}
				}
			}
		}

		$encodeAlias = (is_string($entry->getAlias())) ? rawurlencode($entry->getAlias()) : "";

		$this->addLink("category_link", array(
			"link"=>$this->categoryUrl . $encodeAlias,
			"soy2prefix"=>"cms"
		));

		$this->addLink("category_short_link", array(
			"link"=>$this->categoryUrl . $id,
			"soy2prefix"=>"cms"
		));

		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");
		$this->createAdd("category_name","CMSLabel",array(
			"text"=>(SOYCMS_PUBLISH_LANGUAGE == "jp") ? $entry->getBranchName() : $entry->getOpenLabelCaption(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_alias","CMSLabel",array(
			"text"=>$entry->getAlias(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("label_id","CMSLabel",array(
			"text"=>$entry->getid(),
			"soy2prefix"=>"cms"
		));

		$isDescription = (strlen(trim((string)$entry->getDescription())) > 0);
		$this->addModel("is_description", array(
			"visible" => $isDescription,
			"soy2prefix" => "cms"
		));

		$this->addModel("no_description", array(
			"visible" => !$isDescription,
			"soy2prefix" => "cms"
		));

		$this->createAdd("category_description", "CMSLabel", array(
			"text" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));

		$this->addLabel("category_description_raw", array(
			"html" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));

		$arg = substr(rtrim($_SERVER["REQUEST_URI"], "/"), strrpos(rtrim($_SERVER["REQUEST_URI"], "/"), "/") + 1);
		$this->addModel("is_current_category", array(
			"visible" => ($arg === $encodeAlias),
			"soy2prefix" => "cms"
		));
		$this->addModel("no_current_category", array(
			"visible" => ($arg !== $encodeAlias),
			"soy2prefix" => "cms"
		));

		$this->addLabel("color", array(
			"text" => sprintf("%06X",$entry->getColor()),
			"soy2prefix" => "cms"
		));

		$this->addLabel("background_color", array(
			"text" => sprintf("%06X",$entry->getBackGroundColor()),
			"soy2prefix" => "cms"
		));

		$this->addLabel("entry_count", array(
			"text" => (is_array($this->entryCount) && isset($this->entryCount[$id]) && is_numeric($this->entryCount[$id])) ? $this->entryCount[$id] : 0,
			"soy2prefix" => "cms"
		));

		CMSPlugin::callEventFunc('onLabelOutput',array("labelId"=>$id,"SOY2HTMLObject"=>$this,"label"=>$entry));
	}

	function setCategoryUrl($categoryUrl){
		$this->categoryUrl = $categoryUrl;
	}

	function setEntryCount($entryCount) {
		$this->entryCount = $entryCount;
	}
}
