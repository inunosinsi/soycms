<?php

class SearchCustomSearchfieldListComponent extends HTMLList{

	private $conditions = array();
	private $last;
	private $configs = array();

	protected function populateItem($entity, $key, $int){
		$fieldId = (is_string($key)) ? $key : "";
		$typ = (isset($entity["type"])) ? $entity["type"] : "";

		$this->addLabel("field_label", array(
			"text" => (isset($entity["label"]) && is_string($entity["label"])) ? $entity["label"] : ""
		));

		$this->addModel("colspan_last", array(
			"attr:colspan" => ($int == $this->last && $this->last%2 === 1) ? "3" : "1"
		));

		$this->addLabel("field_input_html", array(
			"html" => (strlen($fieldId)) ? self::_buildFieldInput($fieldId, $typ) : ""
		));
	}

	/**
	 * @param string, string
	 * @return html
	 */
	private function _buildFieldInput(string $fieldId, string $typ){
		if(!class_exists("CustomSearchFieldUtil")) SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");

		$v = "";
		if(is_array($this->conditions) && strlen($fieldId) && isset($this->conditions[$fieldId])){
			if(is_string($this->conditions[$fieldId])) {
				$v = htmlspecialchars($this->conditions[$fieldId], ENT_QUOTES, "UTF-8");
			}else if(is_array($this->conditions[$fieldId])){
				$v = $this->conditions[$fieldId];
			}
		}
		$name = "searchfield[" . $fieldId . "]";
		switch($typ){
			case CustomSearchFieldUtil::TYPE_CHECKBOX:
				if(!isset($this->configs[$fieldId]["option"])) return "";
				$opts = explode("\n", trim((string)$this->configs[$fieldId]["option"]));
				if(!count($opts)) return "";
				$html = array();
				foreach($opts as $opt){
					$opt = htmlspecialchars(trim((string)$opt), ENT_QUOTES, "UTF-8");
					if(!strlen($opt)) continue;
					if(is_array($v) && count($v) && is_numeric(array_search($opt, $v))){
						$html[] = "<label><input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $opt . "\" checked=\"checked\">" . $opt . "</label>";
					}else{
						$html[] = "<label><input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $opt . "\">" . $opt . "</label>";
					}
				}
				return implode("\n", $html);
			case CustomSearchFieldUtil::TYPE_RADIO:
				if(!isset($this->configs[$fieldId]["option"])) return "";
				$opts = explode("\n", trim((string)$this->configs[$fieldId]["option"]));
				if(!count($opts)) return "";
				$html = array();
				foreach($opts as $opt){
					$opt = htmlspecialchars(trim((string)$opt), ENT_QUOTES, "UTF-8");
					if(!strlen($opt)) continue;
					if($opt == $v){
						$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" checked=\"checked\">" . $opt . "</label>";
					}else{
						$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\">" . $opt . "</label>";
					}
				}
				return implode("\n", $html);
			case CustomSearchFieldUtil::TYPE_SELECT:
				if(!isset($this->configs[$fieldId]["option"])) return "";
				$opts = explode("\n", trim((string)$this->configs[$fieldId]["option"]));
				if(!count($opts)) return "";
				$html = array();
				$html[] = "<select name=\"" . $name . "\" class=\"form-control\">";
				$html[] = "<option value=\"\"></option>";
				foreach($opts as $opt){
					$opt = htmlspecialchars(trim((string)$opt), ENT_QUOTES, "UTF-8");
					if(!strlen($opt)) continue;
					if($opt == $v){
						$html[] = "<option value=\"" . $opt . "\" selected=\"selected\">" . $opt . "</option>";
					}else{
						$html[] = "<option value=\"" . $opt . "\">" . $opt . "</option>";
					}
				}
				$html[] = "</select>";
				return implode("\n", $html);
			case CustomSearchFieldUtil::TYPE_INTEGER:
				return "<input type=\"number\" class=\"form-control\" name=\"" . $name . "\" value=\"" . $v . "\">";
			case CustomSearchFieldUtil::TYPE_RANGE:
				$start = (isset($v["start"])) ? htmlspecialchars($v["start"], ENT_QUOTES, "UTF-8") : "";
				$end = (isset($v["end"])) ? htmlspecialchars($v["end"], ENT_QUOTES, "UTF-8") : "";
				$html = array();
				$html[] = "<input type=\"number\" class=\"form-control\" name=\"" . $name . "[start]\" value=\"" . $start . "\">";
				$html[] = " 〜 ";
				$html[] = "<input type=\"number\" class=\"form-control\" name=\"" . $name . "[end]\" value=\"" . $end . "\">";
				return implode("\n", $html);
			default:
				return "<input type=\"text\" class=\"form-control\" name=\"" . $name . "\" value=\"" . $v . "\">";
		}
	}

	function setConditions($conditions){
		$this->conditions = $conditions;
	}
	function setLast($last){
		$this->last = $last;
	}
	function setConfigs($configs){
		$this->configs = $configs;
	}
}