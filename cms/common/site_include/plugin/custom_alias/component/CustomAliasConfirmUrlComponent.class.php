<?php

class CustomAliasConfirmUrlComponent {

	public static function buildForm($entryId, $entryPageUri=null){
		$alias = CustomAliasUtil::getAliasById($entryId);
		if(!strlen($alias)) return "";

		//確認用のURLだけ表示しておく
		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"custom_alias_input\">カスタムエイリアス</label><br>";
		$html[] = $entryPageUri . $alias . " ";
		$detailPageUrl = htmlspecialchars($entryPageUri.rawurlencode($alias), ENT_QUOTES, "UTF-8");
		$html[] = "<a href=\"".$detailPageUrl."\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-primary\">確認</a>";
		$html[] = "<input type=\"hidden\" id=\"custom_alias_confirm_url\" value=\"" . $detailPageUrl . "\">";
		$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-warning\" onclick=\"custom_alias_copy_url();\">コピー</a>";
		$html[] = "<script>" . file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js") . "</script>";
		$html[] = "</div>";

		return implode("\n", $html);
	}
}
