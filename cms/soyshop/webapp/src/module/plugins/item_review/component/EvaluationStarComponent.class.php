<?php

class EvaluationStarComponent {

	function __construct(){
		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
	}

	public static function buildEvaluateArea($evaluate = 1){
		$config = ItemReviewUtil::getConfig();

		$html = array();
		$html[] = "<span id=\"evaluate_star\"></span>";
		$html[] = "<input type=\"hidden\" name=\"Review[evaluation]\" id=\"evaluate_value\" value=\"" . $evaluate . "\">";
		$html[] = "<input type=\"hidden\" id=\"evaluate_color\" value=\"" . $config["code"] . "\">";
		$html[] = "<script>\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/evaluate.js") . "\n</script>";
		$html[] = "<style>#evaluate_star{-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;}</style>";

		return implode("\n", $html);
	}
}
