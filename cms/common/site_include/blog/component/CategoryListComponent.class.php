<?php

/**
 * カテゴリーを表示
 */
class CategoryListComponent extends HTMLList{

	var $categoryUrl;
	var $entryCount = array();

	protected function populateItem($entry){
		$id = (is_numeric($entry->getId())) ? (int)$entry->getId() : 0;
		$encodeAlias = (is_string($entry->getAlias())) ? rawurlencode($entry->getAlias()) : "";

		$this->addLink("category_link", array(
			"link"=>$this->categoryUrl . $encodeAlias,
			"soy2prefix"=>"cms"
		));

		$this->addLink("category_short_link", array(
			"link"=>$this->categoryUrl . $id,
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_name","CMSLabel",array(
			"text"=>$entry->getBranchName(),
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

		$isDescription = (strlen(trim((string)$entry->getDescription())));
		$this->addModel("is_category_description", array(
			"visible" => $isDescription,
			"soy2prefix" => "cms"
		));

		$this->addModel("no_category_description", array(
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
