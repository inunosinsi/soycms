<?php
SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
class FieldFormComponent {

    public static function buildForm($fieldId, $field, $value = null, $lang = UtilMultiLanguageUtil::LANGUAGE_JP) {

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

                $opt = self::getFieldOption($field["option"], $lang);
                if (strlen($opt) > 0) {
                    $chks = (isset($value)) ? explode(",", $value) : array(); //valuesを配列化
                    $options = explode("\n", $opt);
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
              if(!isset($field["option"])) return "";

                $html = array();

                $opt = self::getFieldOption($field["option"], $lang);
                if (strlen($opt) > 0) {
                    $options = explode("\n", $opt);
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
                if(!isset($field["option"])) return "";

                $html = array();
                $opt = self::getFieldOption($field["option"], $lang);
                if (strlen($opt) > 0) {
                    $options = explode("\n", $opt);
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

    private static function getFieldOption($options, $lang){
        if(isset($options[$lang]) && strlen($options[$lang])) return trim($options[$lang]);

        //日本語カラムを取得
        if(isset($options[UtilMultiLanguageUtil::LANGUAGE_JP])) return trim($options[UtilMultiLanguageUtil::LANGUAGE_JP]);

        return null;
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
