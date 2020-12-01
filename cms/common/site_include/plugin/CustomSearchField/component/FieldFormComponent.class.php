<?php
SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");
class FieldFormComponent {

    public static function buildForm($fieldId, $field, $value = null) {

        switch ($field["type"]) {
            case CustomSearchFieldUtil :: TYPE_STRING :
                return "<input type=\"text\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $value . "\" class=\"form-control\">";

            case CustomSearchFieldUtil :: TYPE_TEXTAREA :
                return "<textarea name=\"custom_search[" . $fieldId . "]\" class=\"form-control\">" . $value . "</textarea>";

            case CustomSearchFieldUtil :: TYPE_RICHTEXT :
                return "<textarea class=\"custom_field_textarea mceEditor\" name=\"custom_search[" . $fieldId . "]\">" . $value . "</textarea>";

            case CustomSearchFieldUtil :: TYPE_INTEGER :
            case CustomSearchFieldUtil :: TYPE_RANGE :
                return "<input type=\"number\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $value . "\" class=\"form-control\" style=\"width:150px;\">";

            case CustomSearchFieldUtil :: TYPE_CHECKBOX :
			    $html = array();
				$isBr = (isset($field["br"]) && $field["br"] == 1);

				$opt = $field["option"];
                if (strlen($opt) > 0) {
                    $chks = (isset($value)) ? explode(",", $value) : array(); //valuesを配列化
					$options = explode("\n", $opt);
					$html[] = "<div class=\"form-inline\">";
                    foreach ($options as $option) {
                        $oVal = trim($option);
                        if (in_array($oVal, $chks)) {
                            $html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\" checked=\"\"> " . $oVal . "</label>";
                        } else {
                            $html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $fieldId . "][]\" value=\"" . $oVal . "\"> " . $oVal . "</label>";
                        }
						if($isBr) $html[] = "<br>";
                    }

					//その他
					if(isset($field["other"]) && (int)$field["other"] === 1){
						$html[] = "<label>その他：<input type=\"text\" name=\"custom_search[" . $fieldId . "][]\" value=\"" . self::_getOtherValue($chks, $options) . "\"></label>";
					}

					$html[] = "</div>";
                }
                return implode("\n", $html);

            case CustomSearchFieldUtil :: TYPE_RADIO :
              if(!isset($field["option"])) return "";

                $html = array();
				$isBr = (isset($field["br"]) && $field["br"] == 1);
				$isOptMatch = false;	//選択した値があって、optionsの方で一致したものがあればtrue、なければotherの値にする

                $opt = $field["option"];
                if (strlen($opt) > 0) {
                    $options = explode("\n", $opt);
					$html[] = "<div class=\"form-inline\">";
					foreach ($options as $option) {
                        $oVal = trim($option);
                        if (isset($value) && $oVal === $value) {
                            $html[] = "<label><input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
							$isOptMatch = true;
                        } else {
                            $html[] = "<label><input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
                        }
						if($isBr) $html[] = "<br>";
                    }
					$html[] = "</div>";

					//その他
					if(isset($field["other"]) && (int)$field["other"] === 1){
						$otherValue = (strlen($value) && !$isOptMatch) ? htmlspecialchars($value, ENT_QUOTES, "UTF-8") : "";
						$html[] = "<label>";
						if(strlen($otherValue)){
							$html[] = "<input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"" . $otherValue . "\" id=\"" . $fieldId . "_other\" checked=\"checked\">";
						}else{
							$html[] = "<input type=\"radio\" name=\"custom_search[" . $fieldId . "]\" value=\"\" id=\"" . $fieldId . "_other\">";
						}
						$html[] = "その他 <input type=\"text\" value=\"" . $otherValue . "\" onchange=\"$('#" . $fieldId . "_other').val($(this).val());\">";
						$html[] = "</label>";
					}
                }

                return implode("\n", $html);

            case CustomSearchFieldUtil :: TYPE_SELECT :
                if(!isset($field["option"])) return "";

                $html = array();
                $opt = $field["option"];
                if (strlen($opt) > 0) {
                    $options = explode("\n", $opt);
					$html[] = "<div>";
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
					$html[] = "<div>";
                }

                return implode("\n", $html);
        }
    }

	//その他の値を取得する
	private static function _getOtherValue($chks, $opts){
		foreach($chks as $chk){
			//チェックした値がoptionsの中になければ空の値とする
			$isOpt = false;
			foreach($opts as $opt){
				if($chk == trim($opt)){
					$isOpt = true;
					break;
				}
			}
			if(!$isOpt) return htmlspecialchars($chk, ENT_QUOTES, "UTF-8");
		}
		return "";
	}

    public static function buildSearchConditionForm($fieldId, $field, $cnd, $lang = UtilMultiLanguageUtil::LANGUAGE_JP) {
        $form = self::buildForm($fieldId, $field, null, $lang);
        $form = str_replace("custom_search", "search_condition", $form);

        switch($field["type"]){
            case CustomSearchFieldUtil :: TYPE_TEXTAREA :
            case CustomSearchFieldUtil :: TYPE_RICHTEXT :
                if(strpos($form, "mceEditor")){
                    $form = str_replace(" mceEditor", "", $form);
                }
                if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
                    $form = str_replace("</textarea>", $cnd[$fieldId] . "</textarea>", $form);
                }
                break;

            case CustomSearchFieldUtil :: TYPE_CHECKBOX:
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
            case CustomSearchFieldUtil :: TYPE_RADIO:
                $forms = explode("\n", $form);
                if(!count($forms)) break;
                $fs = array();
                foreach($forms as $f){
                    preg_match('/value="(.*)"/', $f, $tmp);
                    if(isset($tmp[1]) && isset($cnd[$fieldId]) && $tmp[1] ==  $cnd[$fieldId]){
                        $f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" checked=\"checked\"", $f);
                        $fs[] = $f;
                    }else{
                        $fs[] = $f;
                    }
                }
                $form = implode("\n", $fs);
            case CustomSearchFieldUtil :: TYPE_SELECT:
                $forms = explode("\n", $form);
                if(!count($forms)) break;
                $fs = array();
                foreach($forms as $f){
                    preg_match('/value="(.*)"/', $f, $tmp);
                    if(isset($tmp[1]) && isset($cnd[$fieldId]) && $tmp[1] ==  $cnd[$fieldId]){
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
