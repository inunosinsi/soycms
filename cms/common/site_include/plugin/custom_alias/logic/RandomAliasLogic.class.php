<?php

class RandomAliasLogic extends SOY2LogicBase {

	function getLabelList(){
		try{
			$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		}catch(Exception $e){
			return array();
		}
		if(!count($labels)) return array();

		$list = array();
		foreach($labels as $label){
			$list[$label->getId()] = $label->getCaption();
		}
		return $list;
	}

	function buildLabelCheckboxes($list, $cnf){
		if(!is_array($list) || !count($list)) return "";

		$html = array();
		foreach($list as $labelId => $caption){
			if(is_array($cnf) && count($cnf) && is_numeric(array_search($labelId, $cnf))){
				$html[] = "<label><input type=\"checkbox\" name=\"RandomCnf[label][]\" value=\"" . $labelId . "\" checked=\"checked\">" . $caption . "</label> ";
			}else{
				$html[] = "<label><input type=\"checkbox\" name=\"RandomCnf[label][]\" value=\"" . $labelId . "\">" . $caption . "</label> ";
			}
		}
		return implode("\n", $html);
	}
}
