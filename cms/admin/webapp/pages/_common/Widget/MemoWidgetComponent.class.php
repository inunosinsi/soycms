<?php

class MemoWidgetComponent {

	function buildWidget(){
		$html = array();
		$html[] = "<div id=\"memo_widget\">";
		$html[] = "	<button class=\"btn btn-success\" id=\"memo_widget_button\">▲メモ</button>";
		$html[] = "	<div id=\"memo_widget_textarea\">";
		$html[] = "		<textarea class=\"form-control\" id=\"memo_widget_content\">" . SOY2DAOFactory::create("admin.MemoDAO")->getLatestMemo()->getContent() . "</textarea>";
		$html[] = "		<div>";
		$html[] = "			<button class=\"btn btn-info\" id=\"memo_widget_save_button\">保存</button>";
		$html[] = "		</div>";
		$html[] = "	</div>";
		$html[] = "<input type=\"hidden\" id=\"memo_widget_save_path\" value=\"" . SOY2PageController::createLink("Memo.Save") . "\">";
		$html[] = "<input type=\"hidden\" id=\"memo_widget_load_path\" value=\"" . SOY2PageController::createLink("Memo.Load") . "\">";
		$html[] = "</div>";

		$html[] = "<style>";
		$html[] = file_get_contents(dirname(__FILE__) . "/css/widget.css");
		$html[] = "</style>";

		$html[] = "<script>";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/widget.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}
}
