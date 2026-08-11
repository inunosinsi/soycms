<?php
class GroupFieldFormComponent {

	public static function buildForm($fieldId, $field, $groupId, $value = null, $isMyPage = false, $hasStyle = false, $lat = null, $lng = null) {

		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
		$nameProperty = "user_group_custom[" . $fieldId . "]";

		switch ($field["type"]) {
			case UserGroupCustomSearchFieldUtil :: TYPE_STRING :
				if($hasStyle){
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:100%;\">";
				}else{
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
				}

			case UserGroupCustomSearchFieldUtil :: TYPE_TEXTAREA :
				if($hasStyle){
					return "<textarea name=\"" . $nameProperty . "\" style=\"width:100%;\">" . $value . "</textarea>";
				}else {
					return "<textarea name=\"" . $nameProperty . "\">" . $value . "</textarea>";
				}

			case UserGroupCustomSearchFieldUtil :: TYPE_RICHTEXT :
				return "<textarea class=\"custom_field_textarea mceEditor\" name=\"" . $nameProperty . "\">" . $value . "</textarea>";

			case UserGroupCustomSearchFieldUtil :: TYPE_INTEGER :
			case UserGroupCustomSearchFieldUtil :: TYPE_RANGE :
				return "<input type=\"number\" name=\"" . $nameProperty  ."\" value=\"" . $value . "\">";

			case UserGroupCustomSearchFieldUtil :: TYPE_CHECKBOX :
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

			case UserGroupCustomSearchFieldUtil :: TYPE_RADIO :
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

			case UserGroupCustomSearchFieldUtil :: TYPE_SELECT :
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

			case UserGroupCustomSearchFieldUtil :: TYPE_DATE :
				$value = (strlen($value)) ? date("Y-m-d", $value) : null;
				return "<input type=\"text\" class=\"date_picker_start\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" readonly=\"readonly\">";

			case UserGroupCustomSearchFieldUtil :: TYPE_MAP :
				$html = array();
				$html[] = "<input type=\"text\" id=\"address\"  name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:80%;\"><br>";
				$html[] = "<a href=\"javascript:void(0)\" id=\"search_by_address\">住所から地図検索</a>";
				$html[] = "<input type=\"hidden\" id=\"lat\" name=\"map_lat\" value=\"" . $lat . "\">";
				$html[] = "<input type=\"hidden\" id=\"lng\" name=\"map_lng\" value=\"" . $lng . "\">";
				$html[] = "<div id=\"map\"></div>";

				return implode("\n", $html);

			case UserGroupCustomSearchFieldUtil :: TYPE_IMAGE :
				$html = array();
				if(strlen($value)){
					$path = UserGroupCustomSearchFieldUtil::getFilePath($groupId, $value);
					//$html[] = "<a href=\"" . $path . "\" target=\"_blank\"><img src=\"" . SOYSHOP_SITE_URL . "im.php?src=" . $path . "&width=150" . "\"></a>";
					$html[] = "<a href=\"" . $path . "\" target=\"_blank\"><img src=\"" . $path . "\"></a>";
					$html[] = "<input type=\"hidden\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
					$html[] = "<label><input type=\"checkbox\" name=\"image_delete[" . $fieldId . "]\">削除</label>";
				}
				$html[] = "<p><input type=\"file\" name=\"" . $nameProperty . "\"></p>";
				return implode("\n", $html);
		}
	}

	public static function buildSearchConditionForm($fieldId, $field, $cnd) {
		$form = self::buildForm($fieldId, $field);
		$form = str_replace("user_custom_search", "search_condition", $form);

		switch($field["type"]){
			case UserGroupCustomSearchFieldUtil :: TYPE_TEXTAREA :
			case UserGroupCustomSearchFieldUtil :: TYPE_RICHTEXT :
				if(strpos($form, "mceEditor")){
					$form = str_replace(" mceEditor", "", $form);
				}
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("</textarea>", $cnd[$fieldId] . "</textarea>", $form);
				}
				break;

			case UserGroupCustomSearchFieldUtil :: TYPE_CHECKBOX:
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
			case UserGroupCustomSearchFieldUtil :: TYPE_RADIO:
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
			case UserGroupCustomSearchFieldUtil :: TYPE_SELECT:
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
			case UserGroupCustomSearchFieldUtil :: TYPE_IMAGE:
				//検索対象外
				break;
			default:
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("value=\"\"", "value=\"" . htmlspecialchars($cnd[$fieldId], ENT_QUOTES, "UTF-8") . "\"", $form);
				}
		}

		return $form;
	}
}
