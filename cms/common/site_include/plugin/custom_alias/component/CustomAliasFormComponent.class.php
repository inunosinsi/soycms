<?php

class CustomAliasFormComponent {

	public static function buildForm($mode, $entryId, $entryPageUri=null){
		$alias = CustomAliasUtil::getAliasById($entryId);

		$isBlogPage = (strlen($entryPageUri));
		$label = "カスタムエイリアス";
		if($isBlogPage) $label .= "（ブログのエントリーページのURL）";

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"custom_alias_input\">" . $label . "</label>";

		$html[] = "<div class=\"form-inline\">";
		if($isBlogPage) $html[] = $entryPageUri;

		switch($mode){
			case CustomAliasUtil::MODE_RANDOM:
				$cnf = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_RANDOM);
				if(isset($cnf["label"]) && is_array($cnf["label"]) && count($cnf["label"])){
					//ラベル毎に使用モード
					$html[] = "<input value=\"" . $alias . "\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" class=\"form-control\" style=\"width:40%;\">";
					if(!strlen($alias)){
						$html[] = "<input value=\"" . CustomAliasUtil::generateRandomString() . "\" id=\"custom_alias_random_value\" type=\"hidden\">";
						$html[] = "<input value=\"" . $alias . "\" id=\"custom_alias_setting_value\" type=\"hidden\">";	//不要なコードだけれども、いつか使うかもしれないから念のために残しておく
						$html[] = "<script>";
						$html[] = "var random_alias_config = [" . implode(",", $cnf["label"]) . "];";
						$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/random.js");
						$html[] = "</script>";
					}
				}else{
					//ランダムな文字列の生成
					if(!strlen($alias)) $alias = CustomAliasUtil::generateRandomString();
					$html[] = "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" class=\"form-control\" style=\"width:40%;\">";
				}

				$html[] = "<script>";
				$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/generate.js");
				$html[] = "</script>";
				$html[] = "<input type=\"button\" class=\"btn btn-primary\" value=\"ランダムな値を挿入\" onclick=\"cusotm_alias_insert_generated_random_value('" . CustomAliasUtil::generateRandomString() . "');\">";

				break;
			default:
				$html[] = "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" class=\"form-control\" style=\"width:40%;\">";
		}


		$detailPageUrl = htmlspecialchars($entryPageUri.rawurlencode($alias), ENT_QUOTES, "UTF-8");
		if($isBlogPage && CustomAliasUtil::getEntryById($entryId)->isActive() > 0){
			$html[] = "<a href=\"".$detailPageUrl."\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-primary\">確認</a>";
		}

		if($isBlogPage && strlen($alias)){
			$html[] = "<input type=\"hidden\" id=\"custom_alias_confirm_url\" value=\"" . $detailPageUrl . "\">";
			$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-warning\" onclick=\"custom_alias_copy_url();\">コピー</a>";
			$html[] = "<script>" . file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js") . "</script>";
		}

		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}
}
