<?php

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		$list = SOY2ActionFactory::createInstance("EntryTemplate.TemplateListAction")->run()->getAttribute("list");

		if(empty($list)){
			DisplayPlugin::hide("has_template");
		}else{
			DisplayPlugin::hide("no_template");
		}

		$this->createAdd("template_list", "_component.Entry.EntryTemplateListComponent",array(
			"list" => $list,
			"labels" => self::_labelList()
		));
	}

	private function _labelList(){
		$res = $this->run("Label.LabelListAction");
		if($res->success()){
			return $res->getAttribute("list");
		}

		return array();
	}
}
