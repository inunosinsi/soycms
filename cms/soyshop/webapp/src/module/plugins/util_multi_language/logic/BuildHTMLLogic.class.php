<?php

class BuildHTMLLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
	}

	function buildHTML($target, $type, $lang, $languageName){
		$config = UtilMultiLanguageUtil::getMailConfig($target, $type, $lang);

		$htmls = array();

		$htmls[] = "<h1>" . $languageName . "版用</h1>";

		$htmls[] = "<dl>";
		$htmls[] = "<dt>自動送信設定</dt>";
		$htmls[] = "<dd>";
		if(!isset($config["active"]) || $config["active"] == 1){
			$htmls[] = "<input name=\"Config[" . $lang . "][active]\" value=\"1\" type=\"radio\" id=\"active_is_" . $lang . "\" checked=\"checked\"><label for=\"active_is_" . $lang . "\">送信する</label>";
			$htmls[] = "<input name=\"Config[" . $lang . "][active]\" value=\"0\" type=\"radio\" id=\"active_no_" . $lang . "\"><label for=\"active_no_" . $lang . "\">送信しない</label>";
		}else{
			$htmls[] = "<input name=\"Config[" . $lang . "][active]\" value=\"1\" type=\"radio\" id=\"active_is_" . $lang . "\"><label for=\"active_is_" . $lang . "\">送信する</label>";
			$htmls[] = "<input name=\"Config[" . $lang . "][active]\" value=\"0\" type=\"radio\" id=\"active_no_" . $lang . "\" checked=\"checked\"><label for=\"active_no_" . $lang . "\">送信しない</label>";
		}

		$htmls[] = "<dt>メール本文出力設定</dt>";
		$htmls[] = "<dd>";

		$txt = "システム(購入状況等)から出力される注文詳細等のメール本文をヘッダーとフッター間に挿入する";
		if(isset($config["output"]) && $config["output"] == 1){
			$htmls[] = "<label><input name=\"Config[" . $lang . "][output]\" value=\"1\" type=\"checkbox\" checked=\"checked\">" . $txt . "</label><br>";
		}else{
			$htmls[] = "<label><input name=\"Config[" . $lang . "][output]\" value=\"1\" type=\"checkbox\">" . $txt . "</label><br>";
		}

		$txt = "プラグイン(配送方法等)から出力される注文詳細等のメール本文をヘッダーとフッター間に挿入する";
		if(isset($config["plugin"]) && $config["plugin"] == 1){
			$htmls[] = "<label><input name=\"Config[" . $lang . "][plugin]\" value=\"1\" type=\"checkbox\" checked=\"checked\">" . $txt . "</label>";
		}else{
			$htmls[] = "<label><input name=\"Config[" . $lang . "][plugin]\" value=\"1\" type=\"checkbox\">" . $txt . "</label>";
		}
		$html[] = "</dd>";

		$title = (isset($config["title"])) ? $config["title"] : "";

		$htmls[] = "</dd>";
		$htmls[] = "<dt>件名</dt>";
		$htmls[] = "<dd>";
		$htmls[] = "<input name=\"Config[" . $lang . "][title]\" value=\"" . htmlspecialchars($title,ENT_QUOTES,"UTF-8") . "\" class=\"title\">";
		$htmls[] = "</dd>";
		$htmls[] = "</dl>";

		$htmls[] = "<table class=\"table table-striped\" style=\"table-layout:auto;\">";
		$htmls[] = "<caption>本文</caption>";

		$array = array("header" => "ヘッダー", "footer" => "フッター");
		foreach($array as $pos => $value){
			$content = (isset($config[$pos])) ? $config[$pos] : "";

			$htmls[] = "<tr>";
			$htmls[] = "<th colspan=\"2\">" . $value . "</th>";
			$htmls[] = "</tr>";
			$htmls[] = "<tr class=\"last_row\">";
			$htmls[] = "<td>";
			$htmls[] = "<textarea name=\"Config[" . $lang . "][" . $pos . "]\" id=\"" . $lang . "_" . $pos . "\" class=\"editor\">" . htmlspecialchars($content,ENT_QUOTES,"UTF-8") . "</textarea>";
			$htmls[] = "</td>";
			$htmls[] = "<td class=\"mail_replace_word_panel\">";

			$htmls[] = self::convertListHTML($lang, $pos);

			$htmls[] = "</td>";
			$htmls[] = "</tr>";
		}
		$htmls[] = "</table>";

		return implode("\n", $htmls);
	}

	private function convertListHTML($lang, $pos){
		$htmls = array();
		$htmls[] = "<a href=\"javascript:void(0);\" class=\"btn btn-default\" onclick=\"$(this).parent().addClass('actived');$(this).hide();\">&lt;&lt;</a>";
		$htmls[] = "<div class=\"word_list\">";
		$htmls[] = "<h5>置換文字列</h5>";
		$htmls[] = "<ul>";

		$convertList = array(
							"NAME" => "氏名",
							"READING" => "フリガナ",
							"MAILADDRESS" => "メールアドレス",
							"BIRTH_YEAR" => "誕生日(年)",
							"BIRTH_MONTH" => "誕生日(月)",
							"BIRTH_DAY" => "誕生日(日)",
							"SHOP_NAME" => "ショップ名",
							"COMPANY_NAME" => "会社名",
							"COMPANY_ADDRESS1" => "会社郵便番号",
							"COMPANY_ADDRESS2" => "会社住所",
							"COMPANY_TELEPHONE" => "会社電話番号",
							"COMPANY_FAX" => "会社FAX番号",
							"COMPANY_MAILADDRESS" => "会社メールアドレス",
							"ORDER_ID" => "注文番号",
							"ORDER_RAWID" => "注文ID",
						);
		foreach($convertList as $key => $value){
			$htmls[] = "<li><a href=\"javascript:void(0);\" onclick=\"$('#" . $lang . "_" . $pos . "').textarea().insertHTML('#" . $key . "#');\">" . $value . "</a></li>";
		}

		$htmls[] = "</ul>";
		$htmls[] = "</div>";

		return implode("\n", $htmls);
	}
}
?>
