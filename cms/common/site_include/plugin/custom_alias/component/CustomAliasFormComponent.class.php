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

		if($isBlogPage && CustomAliasUtil::getEntryById($entryId)->isActive() > 0){
			$html[] = "<a href=\"".htmlspecialchars($entryPageUri.rawurlencode($alias), ENT_QUOTES, "UTF-8")."\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-primary\">確認</a>";
		}
		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}
}
