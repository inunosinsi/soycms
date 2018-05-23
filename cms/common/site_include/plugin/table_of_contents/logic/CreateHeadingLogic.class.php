<?php

class CreateHeadingLogic extends SOY2LogicBase {

	private $headingList = array();

	function createHeading($array){
		return self::buildList($array);
	}

	function getHeadingList(){
		return $this->headingList;
	}

	//$iに階層の深さを入れる
	private function buildList($array, $i = 1){
		$html = array();
		$html[] = "<ul>";
		foreach($array as $values){
			if(isset($values["children"])){
				$html[] = "<li>" . self::buildAnchorTag($values["title"], $i) . self::buildList($values["children"], $i + 1) . "</li>";
			}else{
				$html[] = "<li>" . self::buildAnchorTag($values["title"], $i) . "</li>";
			}
		}
		$html[] = "</ul>";

		return implode("\n", $html);
	}

	//$iに階層の深さ
	private function buildAnchorTag($v, $i){
		static $j;
		if(is_null($j)) $j = 1;
		$href = "heading-" . $i . "-" . $j++;
		$anchorTag = "<a href=\"#" . $href . "\" class=\"layer-" . $i . "\">" . $v ."</a>";
		$this->headingList[$href] = $v;
		return $anchorTag;
	}
}
