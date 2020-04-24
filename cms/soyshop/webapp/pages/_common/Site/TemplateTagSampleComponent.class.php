<?php

class TemplateTagSampleComponent extends HTMLList{
	
	protected function populateItem($entity, $key, $counter){
		
		$class = "tag_toggle_" . $counter;
		
		$this->addLink("tag_link", array(
			"link" => "javascript:void(0);",
			"text" => $entity["id"],
			"onclick" => "$('." . $class . "').toggle();"
		));
		
		$this->addModel("sample_area", array(
			"style" => "display:none;",
			"class" => $class . " block_body"
		));
		
		$this->addLabel("tag", array(
			"html" => (isset($entity["tag"]) && strlen($entity["tag"]) > 0) ? self::buildTagTable($entity["tag"]) : ""
		));
		
		$this->addLabel("sample", array(
			"html" => (isset($entity["sample"]) && strlen($entity["sample"]) > 0) ? self::buildSamplePreformattedText($entity["sample"])  . "</pre>" : ""
		));
	}
	
	private function buildTagTable($raw){
		
		$htmls = array();
		
		$htmls[] = "<table class=\"form_list\">";
		$htmls[] = "<caption>CMSタグ一覧</caption>";
		$htmls[] = "<thead>";
		$htmls[] = "<tr>";
		$htmls[] = "<th>cms:id</th>";
		$htmls[] = "<th>&nbsp;</th>";
		$htmls[] = "<th>説明</th>";
		$htmls[] = "</tr>";
		$htmls[] = "</thead>";
		$htmls[] = "<tbody>";
		
		foreach(explode("\n", $raw) as $line){
			$values = explode(",", trim($line));
			if(count($values) > 0){
				$htmls[] = "<tr>";
				$htmls[] = "<td>" . $values[0] . "</td>";
				$htmls[] = "<td>" . $values[1] . "</td>";
				$htmls[] = "<td>" . $values[2] . "</td>";
				$htmls[] = "</tr>";
			}
		}
		
		
		$htmls[] = "</tbody>";
		$htmls[] = "</table>";
		
		return implode("\n", $htmls);
	}
	
	private function buildSamplePreformattedText($sample){
		$htmls = array();
		$htmls[] = "<h3>テンプレートへの記述例</h3>";
		$htmls[] = "<pre style=\"border:1px solid #000000;padding:5px;margin:0 25px 10px 25px;\">";
		$htmls[] = $sample;
		$htmls[] = "</pre>";
		
		return implode("\n", $htmls);
	}
}
?>