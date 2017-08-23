<?php

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();
		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateListAction")->run();

		$list = $result->getAttribute("list");

		if(empty($list)){
			DisplayPlugin::hide("has_template");
		}else{
			DisplayPlugin::hide("no_template");
		}


		$this->createAdd("template_list","TemplateList",array("list"=>$list,"labels" => $this->getLabelList()));
	}

	private function getLabelList(){
		$res = $this->run("Label.LabelListAction");
		if($res->success()){
			return $res->getAttribute("list");
		}

		return array();
	}
}

class TemplateList extends HTMLList{

	private $labels;

	protected function populateItem($entity){
		$this->createAdd("title","HTMLLink",array(
			"text"=>(strlen($entity->getName()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") :$entity->getName(),
			"link"=>SOY2PageController::createLink("EntryTemplate.Detail.").$entity->getId()
		));
		$this->createAdd("description","HTMLLabel",array("text"=>strip_tags($entity->getDescription())));
		$this->createAdd("modify_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("EntryTemplate.Detail.").$entity->getId()
		));
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link"=>SOY2PageController::createLink("EntryTemplate.Remove.").$entity->getId()
		));
		$this->createAdd("label","HTMLLabel",array(
			"text" => (isset($this->labels[$entity->getLabelId()])) ? $this->labels[$entity->getLabelId()]->getCaption() : "-"
		));
	}

	public function getLabels() {
		return $this->labels;
	}
	public function setLabels($labels) {
		$this->labels = $labels;
	}
}
