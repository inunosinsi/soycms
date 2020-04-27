<?php

class CustomAliasFormComponent {

	public static function buildForm($mode, $entryId, $entryPageUri=null){
		$alias = CustomAliasUtil::getAliasById($entryId);
		if(!strlen($alias) && $mode == CustomAliasUtil::MODE_RANDOM) {	//ランダムな文字列の生成
			$alias = CustomAliasUtil::generateRandomString(56);
		}

		$isBlogPage = (strlen($entryPageUri));
		$label = "カスタムエイリアス";
		if($isBlogPage) $label .= "（ブログのエントリーページのURL）";

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"custom_alias_input\">" . $label . "</label>";

		$html[] = "<div class=\"form-inline\">";
		if($isBlogPage) $html[] = $entryPageUri;

		$html[] = "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" class=\"form-control\" style=\"width:40%;\" />";

		$detailPageUrl = htmlspecialchars($entryPageUri.rawurlencode($alias), ENT_QUOTES, "UTF-8");
		if($isBlogPage && CustomAliasUtil::getEntryById($entryId)->isActive() > 0){
			$html[] = "<a href=\"".$detailPageUrl."\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-primary\">確認</a>";
		}

		if(strlen($alias)){
			$html[] = "<input type=\"hidden\" id=\"custom_alias_confirm_url\" value=\"" . $detailPageUrl . "\">";
			$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-warning\" onclick=\"custom_alias_copy_url();\">コピー</a>";
			$html[] = "<script>" . file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js") . "</script>";
		}

		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}
}
