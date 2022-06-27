<?php
SOY2DAOFactory::importEntity("cms.Page");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		$result = SOY2ActionFactory::createInstance("Template.TemplateListAction")->run();
		$list = $result->getAttribute("list");

		$this->addModel("no_template_message", array(
			"visible" => (!count($list))
		));
		if(count($list) == 0){
			DisplayPlugin::hide("must_exists_template");
		}
		$this->createAdd("template_list", "_component.Template.TemplateListComponent", array(
			"list" => $list
		));
	}

}

