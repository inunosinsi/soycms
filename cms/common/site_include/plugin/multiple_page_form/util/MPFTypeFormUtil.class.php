<?php

class MPFTypeFormUtil {

	const TYPE_INPUT = "input";
	const TYPE_TEXTAREA = "textarea";
	const TYPE_CHECKBOX = "checkbox";
	const TYPE_RADIO = "radio";
	const TYPE_SELECT = "select";
	const TYPE_EMAIL = "mailaddress";

	public static function getTypeList(){
		return self::_types();
	}

	public static function getTypeText($type){
		$types = self::_types();
		return (isset($types[$type])) ? $types[$type] : $types[self::TYPE_INPUT];
	}

	public static function removeItem($hash, $idx, $cnf){
		if(!isset($cnf["item"]) || !is_array($cnf["item"]) || !count($cnf["item"])) return;

		$items = $cnf["item"];
		if(!isset($items[$idx])) return;

		unset($items[$idx]);

		//整列
		if(count($items)){
			$align = array();
			$order = 1;
			foreach($items as $item){
				$item["order"] = $order++;
				$align[] = $item;
			}
			$items = $align;
		}

		$cnf["item"] = $items;

		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		MultiplePageFormUtil::savePageConfig($hash, $cnf);
	}

	public static function getForm($idx, $cnf, $value, $isFirstView=false){
		$html = array();
		$isReq = (isset($cnf["required"]) && (int)$cnf["required"]);
		$attr = (isset($cnf["attribute"])) ? $cnf["attribute"] : "";
		$reqProp = ($isReq) ? "required=\"required\"" : "";

		$name = "MPF[form_" . $idx . "]";

		//$value = null;
		switch($cnf["type"]){
			case self::TYPE_INPUT:
				$typeProp = (isset($cnf["inputType"]) && strlen($cnf["inputType"])) ? htmlspecialchars($cnf["inputType"], ENT_QUOTES, "UTF-8") : "text";
				$html[] = "<input type=\"" . $typeProp . "\" name=\"" . $name . "\" value=\"" . $value . "\" " . $attr . " " . $reqProp . ">";
				break;
			case self::TYPE_TEXTAREA:
				$html[] = "<textarea name=\"" . $name . "\" " . $attr . " " . $reqProp . ">" . $value . "</textarea>";
				break;
			case self::TYPE_CHECKBOX:
				$opts = self::_getOpts($cnf);
				if(count($opts)){
					$checked = (strlen($value)) ? explode(",", $value) : array();
					for($i = 0; $i < count($opts); $i++){	//フォームの初回表示の場合の対策
						if(strpos($opts[$i], "*") === 0){
							$opts[$i] = substr($opts[$i], 1);
							if($isFirstView) $checked[] = $opts[$i];
						}
					}

					if($isReq){
						$fn = (!count($checked)) ? "required=\"required\" " : "";
						$fn .= "onclick=\"mpf_checkbox_required(this);\"";
					}else{
						$fn = "";
					}
					foreach($opts as $opt){
						if(strpos($attr, "class=\"") !== false){
							$chkAttr = str_replace("class=\"", "class=\"mpf_form_" . $idx . " ", $attr);
						}else{
							$chkAttr .= " class=\"mpf_form_" . $idx . "\"";
						}
						
						if(count($checked) && is_numeric(array_search($opt, $checked))){
							$html[] = "<label><input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $opt . "\" " . $chkAttr . " " . $fn . " checked=\"checked\"> " . $opt . "</label>";
						}else{
							$html[] = "<label><input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $opt . "\" " . $chkAttr . " " . $fn . "> " . $opt . "</label>";
						}
					}
				}

				break;
			case self::TYPE_RADIO:
				$opts = self::_getOpts($cnf);
				if(count($opts)){
					for($i = 0; $i < count($opts); $i++){	//フォームの初回表示の場合の対策
						if(strpos($opts[$i], "*") === 0){
							$opts[$i] = substr($opts[$i], 1);
							if($isFirstView) $value = $opts[$i];
						}
					}

					$isFirst = true;
					foreach($opts as $opt){
						if($isFirst){
							if($value == $opt){
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" " . $reqProp . " checked=\"checkec\"> " . $opt . "</label>";
							}else{
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" " . $reqProp . "> " . $opt . "</label>";
							}

							$isFirst = false;
						}else{
							if($value == $opt){
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" checked=\"checked\"> " . $opt . "</label>";
							}else{
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\"> " . $opt . "</label>";
							}
						}
					}
				}
				break;
			case self::TYPE_SELECT:
				$html[] = "<select name=\"" . $name . "\" " . $attr . " " . $reqProp . ">";
				$html[] = "<option></option>";
				$opts = self::_getOpts($cnf);
				if(count($opts)){
					for($i = 0; $i < count($opts); $i++){	//フォームの初回表示の場合の対策
						if(strpos($opts[$i], "*") === 0){
							$opts[$i] = substr($opts[$i], 1);
							if($isFirstView) $value = $opts[$i];
						}
					}
					foreach($opts as $opt){
						if($value == $opt){
							$html[] = "<option selected=\"selected\"> " . $opt . "</option>";
						} else {
							$html[] = "<option> " . $opt . "</option>";
						}
					}
				}
				$html[] = "</select>";
				break;
			case self::TYPE_EMAIL:
				$html[] = "<input type=\"email\" name=\"" . $name . "\" value=\"" . $value . "\" " . $attr . " " . $reqProp . ">";
				break;
		}
		return implode("\n", $html);
	}

	/** private method **/

	private static function _getOpts($cnf){
		if(!isset($cnf["option"]) || !strlen($cnf["option"])) return array();
		$opts = explode("\n", $cnf["option"]);
		if(!count($opts)) return array();

		for($i = 0; $i < count($opts); $i++){
			$opt = trim($opts[$i]);
			if(!strlen($opt)) continue;
			$opts[$i] = $opt;
		}

		return $opts;
	}

	private static function _types(){
		return array(
			self::TYPE_INPUT => "一行テキスト",
			self::TYPE_TEXTAREA => "複数行テキスト",
			self::TYPE_CHECKBOX => "チェックボックス",
			self::TYPE_RADIO => "ラジオボタン",
			self::TYPE_SELECT => "セレクトボックス",
			self::TYPE_EMAIL => "メールアドレス"
		);
	}
}
