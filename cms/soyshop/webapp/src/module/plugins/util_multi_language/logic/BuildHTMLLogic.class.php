<?php

class BuildHTMLLogic extends SOY2LogicBase{
	
	function BuildHTMLLogic(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
	}
	
	function buildHTML($target, $type, $lang){
		$config = UtilMultiLanguageUtil::getMailConfig($target, $type, $lang);
		
		$htmls = array();
		
		$htmls[] = "<h1>英語版用</h1>";
		
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
		
		$title = (isset($config["title"])) ? $config["title"] : "";
		
		$htmls[] = "</dd>";
		$htmls[] = "<dt>件名</dt>";
		$htmls[] = "<dd>";
		$htmls[] = "<input name=\"Config[" . $lang . "][title]\" value=\"" . $title . "\" class=\"title\">";
		$htmls[] = "</dd>";
		$htmls[] = "</dl>";

		$htmls[] = "<table class=\"form_table\" style=\"table-layout:auto;\">";
		$htmls[] = "<caption>本文</caption>";
		
		$array = array("header" => "ヘッダー", "footer" => "フッター");
		foreach($array as $pos => $value){
			$content = (isset($config[$pos])) ? $config[$pos] : "";
			
			$htmls[] = "<tr>";
			$htmls[] = "<th colspan=\"2\">" . $value . "</th>";
			$htmls[] = "</tr>";
			$htmls[] = "<tr class=\"last_row\">";
			$htmls[] = "<td>";
			$htmls[] = "<textarea name=\"Config[" . $lang . "][" . $pos . "]\" id=\"" . $lang . "_" . $pos . "\" class=\"editor\">" . $content . "</textarea>";
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
		$htmls[] = "<a href=\"javascript:void(0);\" class=\"button\" onclick=\"$(this).parent().addClass('actived');$(this).hide();\">&lt;&lt;</a>";
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