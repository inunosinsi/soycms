<?php
class FieldFormComponent {

	public static function buildForm($fieldId, $field, $value = null) {
		
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");

		switch ($field["type"]) {
			case UserCustomSearchFieldUtil :: TYPE_STRING :
				return "<input type=\"text\" name=\"user_custom_search[" . $fieldId . "]\" value=\"" . $value . "\" style=\"width:100%;\">";

			case UserCustomSearchFieldUtil :: TYPE_TEXTAREA :
				return "<textarea name=\"user_custom_search[" . $fieldId . "]\" style=\"width:100%;\">" . $value . "</textarea>";

			case UserCustomSearchFieldUtil :: TYPE_RICHTEXT :
				return "<textarea class=\"custom_field_textarea mceEditor\" name=\"user_custom_search[" . $fieldId . "]\">" . $value . "</textarea>";

			case UserCustomSearchFieldUtil :: TYPE_INTEGER :
			case UserCustomSearchFieldUtil :: TYPE_RANGE :
				return "<input type=\"number\" name=\"user_custom_search[" . $fieldId . "]\" value=\"" . $value . "\">";

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
							$html[] = "<label><input type=\"checkbox\" name=\"user_custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"checkbox\" name=\"user_custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
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
							$html[] = "<label><input type=\"radio\" name=\"user_custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"radio\" name=\"user_custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}
				
				return implode("\n", $html);

			case UserCustomSearchFieldUtil :: TYPE_SELECT :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					$html[] = "<select name=\"user_custom_search[" . $fieldId . "]\">";
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
		}
	}
	
	public static function buildSearchConditionForm($fieldId, $field, $cnd) {
		$form = self::buildForm($fieldId, $field);
		$form = str_replace("user_custom_search", "search_condition", $form);
		
		switch($field["type"]){
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
					if(is_array($cnd[$fieldId]) && in_array($tmp[1], $cnd[$fieldId])){
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
					if($tmp[1] ==  $cnd[$fieldId]){
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
					if($tmp[1] ==  $cnd[$fieldId]){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" selected=\"selected\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
				break;
			default:
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("value=\"\"", "value=\"" . htmlspecialchars($cnd[$fieldId], ENT_QUOTES, "UTF-8") . "\"", $form);
				}
		}
		
		return $form;
	}
}
?>