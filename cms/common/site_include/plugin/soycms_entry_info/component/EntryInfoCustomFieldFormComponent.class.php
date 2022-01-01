<?php

class EntryInfoCustomFieldFormComponent {

	public static function build(int $entryId){
		SOY2::import("site_include.plugin.soycms_entry_info.util.EntryInfoUtil");
		$keyword = EntryInfoUtil::getEntryKeyword($entryId);
		$dsp = soycms_get_entry_object($entryId)->getDescription();

		$html = array();
		$html[] = "<script type=\"text/javascript\">";
		$html[] = "var update_count_entry_description = function (ele) {";
		$html[] = "	$(\"#count_entry_description\").html(ele.value.length);";
		$html[] = "	return true;";
		$html[] = "}";
		$html[] = "</script>";
		$html[] = "<div class=\"table-responsive mt-5\">";
		$html[] = "<table class=\"table\">";
		$html[] = "<caption style=\"padding:5px 10px;font-size:1.2em;\">記事のメタ情報設定</caption>";
		$html[] = "<tr>";
		$html[] = "	<td style=\"width:30%\">";
		$html[] = "		<p class=\"sub\">キーワード(カンマ(<b>,</b>)&nbsp;区切りで複数入力)</p>";
		$html[] = "		<input type=\"text\" id=\"soycms_entry_info_keyword\" class=\"form-control\" name=\"keyword\" value=\"" . htmlspecialchars($keyword, ENT_QUOTES, "UTF-8") . "\">";
		$html[] = "	</td>";

		$html[] = "   <td style=\"width:70%\">";
		$html[] = "		<p class=\"sub\">概要(<span id=\"count_entry_description\">" . mb_strlen($dsp) . "</span>文字)</p>";
		$html[] = "		<input type=\"text\" id=\"soycms_entry_info_description\" name=\"description\" class=\"form-control\" value=\"" . htmlspecialchars($dsp, ENT_QUOTES, "UTF-8") . "\" onkeyup=\"return update_count_entry_description(this);\">";
		//$html[] = "		<input type=\"text\" name=\"description\" class=\"form-control\" value=\"" . htmlspecialchars($dsp, ENT_QUOTES, "UTF-8") . "\">";
		$html[] = "	</td>";
		$html[] = "</tr>";
		$html[] = "</table>";
		$html[] = "</div>";
		// $html[] = "<div class=\"alert alert-info\">記事のメタ情報設定</div>";
		// $html[] = "<div class=\"form-group\">";
		// $html[] = "<label>キーワード(カンマ(<b>,</b>)&nbsp;区切りで複数入力)</label>";
		// $html[] = "<div class=\"form-iniine\">";
		// $html[] = "	<input type=\"text\" class=\"form-control\" name=\"keyword\" value=\"" . htmlspecialchars($keyword, ENT_QUOTES, "UTF-8") . "\">";
		// $html[] = "</div>";
		// $html[] = "</div>";
		// $html[] = "<div class=\"form-group\">";
		// $html[] = "<label>概要(<span id=\"count_entry_description\">" . mb_strlen($dsp) . "</span>文字)</label>";
		// $html[] = "<div class=\"form-iniine\">";
		// //$html[] = "	<input type=\"text\" name=\"description\" class=\"form-control\" value=\"" . htmlspecialchars($dsp, ENT_QUOTES, "UTF-8") . "\" onkeyup=\"return update_count_entry_description(this);\">";
		// $html[] = "	<input type=\"text\" name=\"description\" class=\"form-control\" value=\"" . htmlspecialchars($dsp, ENT_QUOTES, "UTF-8") . "\">";
		// $html[] = "</div>";
		// $html[] = "</div>";
		// $html[] = "<div class=\"alert alert-info\">記事のメタ情報設定ここまで</div>";
		return implode("\n", $html);
	}
}