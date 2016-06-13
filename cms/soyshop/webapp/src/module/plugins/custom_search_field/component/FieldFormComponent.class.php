<?php
class FieldFormComponent {

	function buildForm($fieldId, $field, $value = null) {
		
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");

		switch ($field["type"]) {
			case CustomSearchFieldUtil :: TYPE_STRING :
				return "<input type=\"text\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $value . "\" style=\"width:100%;\">";

			case CustomSearchFieldUtil :: TYPE_TEXTAREA :
				return "<textarea name=\"custom_search[" . $fieldId . "]\" style=\"width:100%;\">" . $value . "</textarea>";

			case CustomSearchFieldUtil :: TYPE_RICHTEXT :
				return "<textarea class=\"custom_field_textarea mceEditor\" name=\"custom_search[" . $fieldId . "]\">" . $value . "</textarea>";

			case CustomSearchFieldUtil :: TYPE_INTEGER :
			case CustomSearchFieldUtil :: TYPE_RANGE :
				return "<input type=\"number\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $value . "\">";

			case CustomSearchFieldUtil :: TYPE_CHECKBOX :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$chks = (isset($value)) ? explode(",", $value) : array(); //valuesを配列化
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (in_array($oVal, $chks)) {
							$html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}
				return implode("\n", $html);

			case CustomSearchFieldUtil :: TYPE_RADIO :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (isset($value) && $oVal === $value) {
							$html[] = "<label><input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}
				
				return implode("\n", $html);

			case CustomSearchFieldUtil :: TYPE_SELECT :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					$html[] = "<select name=\"custom_search[" . $fieldId . "]\">";
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
}
?>