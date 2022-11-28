<?php
class FieldFormComponent {

	public static function buildForm($fieldId, $field, $value = null, $isMyPage = false, $hasStyle = true) {

		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		$nameProperty = ($isMyPage) ? "u_search[" . $fieldId . "]" : "user_custom_search[" . $fieldId . "]";

		switch ($field["type"]) {
			case UserCustomSearchFieldUtil :: TYPE_STRING :
				if($hasStyle){
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:100%;\">";
				}else{
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
				}


			case UserCustomSearchFieldUtil :: TYPE_TEXTAREA :
				if($hasStyle){
					return "<textarea name=\"" . $nameProperty . "\" style=\"width:100%;\">" . $value . "</textarea>";
				}else {
					return "<textarea name=\"" . $nameProperty . "\">" . $value . "</textarea>";
				}

			case UserCustomSearchFieldUtil :: TYPE_RICHTEXT :
				return "<textarea class=\"custom_field_textarea mceEditor\" name=\"" . $nameProperty . "\">" . $value . "</textarea>";

			case UserCustomSearchFieldUtil :: TYPE_INTEGER :
			case UserCustomSearchFieldUtil :: TYPE_RANGE :
				return "<input type=\"number\" name=\"" . $nameProperty  ."\" value=\"" . $value . "\">";

			case UserCustomSearchFieldUtil :: TYPE_CHECKBOX :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$chks = array();//valuesを配列化
					if(isset($value)){
						$chks = (is_array($value)) ? $value : explode(",", $value);
					}
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (in_array($oVal, $chks)) {
							$html[] = "<label><input type=\"checkbox\" name=\"" . $nameProperty . "[]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"checkbox\" name=\"" . $nameProperty . "[]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}
				return implode("\n", $html);

			case UserCustomSearchFieldUtil :: TYPE_RADIO :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (isset($value) && $oVal === $value) {
							$html[] = "<label><input type=\"radio\" name=\"" . $nameProperty . "\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"radio\" name=\"" . $nameProperty . "\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}

				return implode("\n", $html);

			case UserCustomSearchFieldUtil :: TYPE_SELECT :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					$html[] = "<select name=\"" . $nameProperty . "\">";
					$html[] = "<option value=\"\"></option>";
					foreach ($options as $option) {
						$oVal = trim($option);
						if (isset($value) && $oVal === $value) {
							$html[] = "<option value=\"" . $oVal . "\" selected=\"selected\">" . $oVal . "</option>";
						} else {
							$html[] = "<option value=\"" . $oVal . "\">" . $oVal . "</option>";
						}
					}
					$html[] = "</select>";

				}

				return implode("\n", $html);
			case UserCustomSearchFieldUtil :: TYPE_DATE :
				$value = (strlen($value)) ? date("Y-m-d", $value) : null;
				return "<input type=\"text\" class=\"date_picker_start\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";

			case UserCustomSearchFieldUtil :: TYPE_URL :
				if($hasStyle){
					return "<input type=\"url\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:100%;\">";
				}else{
					return "<input type=\"url\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
				}

			case UserCustomSearchFieldUtil :: TYPE_MAILADDRESS :
				if($hasStyle){
					return "<input type=\"email\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:100%;\">";
				}else{
					return "<input type=\"email\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
				}
		}
	}

	public static function buildSearchConditionForm($fieldId, $field, $cnd) {
		$form = self::buildForm($fieldId, $field);
		$form = str_replace("user_custom_search", "u_search", $form);

		//ネームプロパティ
		//$nameProperty = (isset($_GET["collective"])) ? "search_condition" : "u_search";

		switch($field["type"]){
			case UserCustomSearchFieldUtil :: TYPE_RANGE:
				$start = (isset($cnd[$fieldId]["start"]) && is_numeric($cnd[$fieldId]["start"])) ? $cnd[$fieldId]["start"] : "";
				$end = (isset($cnd[$fieldId]["end"]) && is_numeric($cnd[$fieldId]["end"])) ? $cnd[$fieldId]["end"] : "";
				$fs = array();
				$fs[] = "<input type=\"number\" name=\"u_search[" . $fieldId . "][start]\" value=\"" . $start . "\">";
				$fs[] = "〜";
				$fs[] = "<input type=\"number\" name=\"u_search[" . $fieldId . "][end]\" value=\"" . $end . "\">";
				$form = implode(" " , $fs);
				break;
			case UserCustomSearchFieldUtil :: TYPE_TEXTAREA :
			case UserCustomSearchFieldUtil :: TYPE_RICHTEXT :
				if(strpos($form, "mceEditor")){
					$form = str_replace(" mceEditor", "", $form);
				}
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("</textarea>", $cnd[$fieldId] . "</textarea>", $form);
				}
				break;

			case UserCustomSearchFieldUtil :: TYPE_CHECKBOX:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if(isset($cnd[$fieldId]) && is_array($cnd[$fieldId]) && in_array($tmp[1], $cnd[$fieldId])){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" checked=\"checked\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
				break;
			case UserCustomSearchFieldUtil :: TYPE_RADIO:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if(isset($cnd[$fieldId]) && $tmp[1] == $cnd[$fieldId]){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" checked=\"checked\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
			case UserCustomSearchFieldUtil :: TYPE_SELECT:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if(isset($cnd[$fieldId]) && isset($tim[1]) && $tmp[1] == $cnd[$fieldId]){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" selected=\"selected\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
				break;
			case UserCustomSearchFieldUtil :: TYPE_DATE:
				$start = (isset($cnd[$fieldId]["start"])) ? $cnd[$fieldId]["start"] : "";
				$end = (isset($cnd[$fieldId]["end"])) ? $cnd[$fieldId]["end"] : "";
				$fs = array();
				$fs[] = "<input type=\"text\" class=\"date_picker_start\" name=\"u_search[" . $fieldId . "][start]\" value=\"" . $start . "\">";
				$fs[] = "〜";
				$fs[] = "<input type=\"text\" class=\"date_picker_end\" name=\"u_search[" . $fieldId . "][end]\" value=\"" . $end . "\">";
				$form = implode(" " , $fs);
				break;
			default:
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("value=\"\"", "value=\"" . htmlspecialchars($cnd[$fieldId], ENT_QUOTES, "UTF-8") . "\"", $form);
				}
		}

		return $form;
	}
}
