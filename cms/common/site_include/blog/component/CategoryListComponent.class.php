<?php

/**
 * カテゴリーを表示
 */
class CategoryListComponent extends HTMLList{

	var $categoryUrl;
	var $entryCount = 0;

	protected function populateItem($entry){
		$id = (is_numeric($entry->getId())) ? (int)$entry->getId() : 0;

		$this->addLink("category_link", array(
			"link"=>$this->categoryUrl . rawurlencode($entry->getAlias()),
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

		$this->createAdd("category_description", "CMSLabel", array(
			"text" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));

		$this->addLabel("category_description_raw", array(
			"html" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));


		$arg = substr(rtrim($_SERVER["REQUEST_URI"], "/"), strrpos(rtrim($_SERVER["REQUEST_URI"], "/"), "/") + 1);
		$alias = rawurlencode($entry->getAlias());
		$this->createAdd("is_current_category", "HTMLModel", array(
			"visible" => ($arg === $alias),
			"soy2prefix" => "cms"
		));
		$this->createAdd("no_current_category", "HTMLModel", array(
			"visible" => ($arg !== $alias),
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
			"text" => (isset($this->entryCount[$id])) ? $this->entryCount[$id] : 0,
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
