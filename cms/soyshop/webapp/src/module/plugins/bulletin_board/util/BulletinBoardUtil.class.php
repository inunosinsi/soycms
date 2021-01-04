<?php

class BulletinBoardUtil {

	public static function getUsageProhibitedHtmlTagList(){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic")->usageProhibitedHtmlTagList();
	}

	public static function shapeHTML($html){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic", array("html" => $html))->shape();
	}

	public static function nl2br($html){
		$html = trim($html);
		if(!strlen($html)) return "";

		$lines = explode("\n", $html);
		$html = "";

		$noBrMode = false;
		$lastLine = count($lines);
		for($i = 0; $i < $lastLine; $i++){
			$line = trim($lines[$i]);
			$html .= $line;

			//<pre>内は改行なし
			if(is_numeric(strpos($line, "<pre>"))) $noBrMode = true;

			//改行なし
			if($line == "<code>") continue;

			if(!$noBrMode && $i < $lastLine - 1) $html .= "<br>";

			//改行なしモードを戻す
			if(is_numeric(strpos($line, "</pre>"))) $noBrMode = false;
			$html .= "\n";
		}

		//</code>
		$html = str_replace("<br>\n</code>", "</code>", $html);

		return trim($html);
	}

	public static function returnHTML($html){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic", array("html" => $html))->return();
	}
}
