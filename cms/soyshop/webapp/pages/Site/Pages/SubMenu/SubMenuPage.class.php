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

		//クラスファイルの再生成の有無はプラグインに任せる
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

	private function _checkClass(){
		if(!file_exists(SOY2::RootDir() . "module/plugins/research_page_class_file/util/ResearchPageClassFileUtil.class.php")) return false;
		SOY2::import("module.plugins.research_page_class_file.util.ResearchPageClassFileUtil");
		return ResearchPageClassFileUtil::checkIncorrectClassFile($this->id);
	}
}
