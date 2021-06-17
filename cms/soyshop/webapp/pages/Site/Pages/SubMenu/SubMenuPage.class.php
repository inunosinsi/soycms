<?php

class SubMenuPage extends WebPage{

	var $id;
	var $page;

    function __construct($args) {
		$this->id = $args[0];
		$this->page = $args[1];

		parent::__construct();

		$this->addLink("page_detail_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->id)
		));

		$this->addLink("page_script_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Script." . $this->id)
		));

		$this->addLink("class_regenerate_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.ReGenerate." . $this->id)
		));

		DisplayPlugin::toggle("regenerate", self::_checkClass());

		self::_loadSubMenu();
    }

	private function _loadSubMenu(){

		$key = "Site.Pages.SubMenu." . ucwords($this->page->getType()) . "MenuPage";

		if(SOY2HTMLFactory::pageExists($key)){
			$this->createAdd("submenu_page", $key, array(
				"arguments" => array($this->id, $this->page)
			));
		}else{

			$this->createAdd("submenu_page", "Site.Pages.SubMenu.DefaultMenuPage", array(
				"arguments" => array($this->id, $this->page)
			));

		}
	}

	/**
	 * 再生成の必要があるかどうか
	 */
	private function _checkClass(){
		$className = $this->page->getCustomClassName();
		$path = SOYSHOP_SITE_DIRECTORY . ".page/" . $this->page->getCustomClassFileName();

		if(!file_exists($path)){
			return true;
		}

		SOY2::import("base.site.SOYShopPageBase");
		SOY2::imports("base.site.pages.*");
		include_once($path);

		$ref = new ReflectionClass($className);

		if($ref->isSubClassOf($this->page->getBaseClassName())){
			//クラス名と同名の関数がある場合はtrueにする
			$code = file_get_contents($path);
			return (is_numeric(strpos($code, "function " . $className)));
		}else{
			return true;
		}
	}
}
