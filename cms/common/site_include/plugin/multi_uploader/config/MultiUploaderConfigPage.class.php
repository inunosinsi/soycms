<?php

class MultiUploaderConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$labelIds = ($_POST["labelIds"]);
			$tmps = array();
			if(count($labelIds)){
				foreach($labelIds as $labelId){
					if(!strlen($labelId) || !is_numeric($labelId)) continue;
					$tmps[] = (int)$labelId;
				}
			}
			$labelIds = $tmps;

			$this->pluginObj->setLabelIds($labelIds);
			CMSPlugin::savePluginConfig(MultiUploaderPlugin::PLUGIN_ID, $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addLabel("label_select", array(
			"html" => self::_buildLabelSelect()
		));

		$this->addLabel("site_id", array(
			"text" => UserInfoUtil::getSite()->getSiteId()
		));
	}

	private function _buildLabelSelect(){
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		if(!count($labels)) return "";

		$html = array();

		$labelIds = $this->pluginObj->getLabelIds();
		if(!is_array($labelIds)) $labelIds = array();
		$labelIds[] = 0;

		foreach($labelIds as $labelId){
			$html[] = "<select name=\"labelIds[]\">";
			$html[] = "	<option></option>";
			foreach($labels as $label){
				if($label->getId() == $labelId){
					$html[] = "	<option value=\"" . $label->getId() . "\" selected>" . $label->getCaption() . "</option>";
				}else{
					$html[] = "	<option value=\"" . $label->getId() . "\">" . $label->getCaption() . "</option>";
				}
			}
			$html[] = "</select>";
		}

		return implode("\n", $html);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
