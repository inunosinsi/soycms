<?php

class ItemOptionUtil {

	const OPTION_TYPE_SELECT = "select";
	const OPTION_TYPE_RADIO = "radio";
	const OPTION_TYPE_TEXT = "text";
	const OPTION_TYPE_TEXTAREA = "textarea";

	public static function getTypes(){
		return array(
			self::OPTION_TYPE_SELECT => "セレクトボックス",
			self::OPTION_TYPE_RADIO => "ラジオボタン",
			self::OPTION_TYPE_TEXT => "テキスト",
			self::OPTION_TYPE_TEXTAREA => "複数行テキスト"
		);
	}

	public static function getOptions(){
		return self::_getOptions();
	}

	public static function saveOptions(array $opts){
		//  soy2_serializeを外す
		//SOYShop_DataSets::put("item_option", soy2_serialize($opts));
		SOYShop_DataSets::put("item_option", $opts);
	}

	/**
	 * @return array
	 */
	private static function _getOptions(){
		static $opts;
		if(is_null($opts)) {
			$opts = SOYShop_DataSets::get("item_option", array());
			if(!is_array($opts)){	//2回シリアライズ問題の回避
				try{
					$res = soyshop_get_hash_table_dao("data_sets")->executeQuery("SELECT object_data FROM soyshop_data_sets WHERE class_name = 'item_option'");
				}catch(Exception $e){
					$res = array();
				}
				if(count($res) && isset($res[0]["object_data"]) && strlen($res[0]["object_data"])){
					$d = $res[0]["object_data"];
					preg_match('/\"(.*)\"/', $d, $tmp);
					if(isset($tmp[0])){
						$d = trim($tmp[0], "\"");
						$opts = soy2_unserialize($d);
					}
				}
			}
			if(!is_array($opts)) $opts = array();
		}
		return $opts;
	}

	public static function getFieldValue(string $key, int $itemId, string $prefix = "jp"){
		return self::_getFieldValue($key, $itemId, $prefix);
	}

	public static function getFieldValueByItemOrderId(string $key, int $itemOrderId, string $prefix = "jp"){
		return self::_getFieldValueByItemOrderId($key, $itemOrderId, $prefix);
	}

	/**
	 * 設定に従いフォームを組み立てる
	 */
	public static function buildOptions(string $key, array $conf, int $itemId, string $prefix="jp"){
		$v = self::_getFieldValue($key, $itemId, $prefix);
		if(!strlen($v)) return "";

		$name = "item_option[" . $key . "]";
		$type = (isset($conf["type"])) ? $conf["type"] : "select";

		return self::_buildOpt($name, $type, $v);
	}

	public static function buildOptionsWithSelected(string $key, array $conf, SOYShop_ItemOrder $itemOrder, string $selected, string $prefix="jp", bool $isBr=true){
		$v = self::_getFieldValue($key, $itemOrder->getItemId(), $prefix);
		if(!strlen($v)) return "";

		$name = "item_option[" . $itemOrder->getId() . "][" . $key . "]";
		$type = (isset($conf["type"])) ? $conf["type"] : "select";

		return self::_buildOpt($name, $type, $v, $selected, $isBr);
	}

	public static function buildOption(string $name, string $type, string $fieldValue, string $selected, bool $isBr=true, bool $editMode=false){
		return self::_buildOpt($name, $type, $fieldValue, $selected, $isBr, $editMode);
	}

	private static function _buildOpt(string $name, string $type, string $fieldValue, string $selected="", bool $isBr=true, bool $editMode=false){
		$opts = explode("\n", trim($fieldValue));
		$selected = self::escapeString($selected);
		if(!strlen($selected)) $selected = null;
		//if(is_null($selected) && $editMode) $type = "text";	//管理画面で編集の場合は選択がnullの場合はテキストフォームを出力する

		//選択したタイプによって、HTMLの出力を変える
		switch($type){
			case self::OPTION_TYPE_TEXT:
				return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\" >";
			case self::OPTION_TYPE_TEXTAREA:
				return "<textarea name=\"" . $name . "\">" . $selected . "</textarea>";
			case self::OPTION_TYPE_RADIO:
				$html = array();
				$first = true;
				foreach($opts as $opt){
					$opt = trim(str_replace(array("\r", "\n"), "", $opt));

					//何らかの値が既に選択されている場合
					if(isset($selected)){
						if($selected == $opt){
							$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" checked=\"checked\">" . $opt . "</label>";
						}else{
							$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\">" . $opt . "</label>";
						}
					}else{
						if($first){
							$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\" checked=\"checked\">" . $opt . "</label>";
							$first = false;
						}else{
							$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $opt . "\">" . $opt . "</label>";
						}
					}
					if($isBr) $html[] = "<br>";	//改行が欲しいか？

				}
				return implode("\n", $html);
			case self::OPTION_TYPE_SELECT:
			default:
				
				$html = array();
				$html[] = "<select name=\"" . $name . "\" required=\"required\">";

				$cnfs = ItemOptionUtil::getOptions();
				preg_match('/\[(.*?)\]/', $name, $tmp);
				
				if(isset($tmp[1]) && isset($cnfs[$tmp[1]])){
					$isIni = (isset($cnfs[$tmp[1]]["initial_value"])) ? (int)$cnfs[$tmp[1]]["initial_value"] : 0;
					if($isIni === 1) $html[] = "<option disabled selected value>" . self::_getInitialValue() . "</option>";	/** @ToDo 要望があれば文言の設定を追加 */
				}

				foreach($opts as $opt){
					$opt = trim(str_replace(array("\r", "\n"), "", $opt));
					if($opt == $selected){
						$html[] = "<option selected=\"selected\">" . $opt . "</option>";
					}else{
						$html[] = "<option>" . $opt . "</option>";
					}
				}
				$html[] = "</select>";
				return implode("\n", $html);
		}
	}

	private static function _getInitialValue(){
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") return "選択してください";

		switch(SOYSHOP_PUBLISH_LANGUAGE){
			case "zh":
				return "请选择";
			case "zh-tw":
				return "請選擇";
			case "en":
			default:
				return "Please select";
		}
	}

	/**
	 * 値を取得するメソッド
	 * @param string key, integer itemId, string prefix
	 * @return object SOYShop_ItemAttribute
	 */
	private static function _getFieldValue(string $k, int $itemId, string $prefix="jp"){
		static $v;
		if(is_null($v)) $v = array();
		if(isset($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];

		$key = "item_option_" . $k;

		if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$v[$prefix][$itemId][$k] = trim(soyshop_get_item_attribute_value($itemId, $key . "_" . self::convertConfigPrefix(SOYSHOP_PUBLISH_LANGUAGE), "string"));
			if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];

			if(SOYSHOP_PUBLISH_LANGUAGE != $prefix){
				$v[$prefix][$itemId][$k] = trim(soyshop_get_item_attribute_value($itemId, $key . "_" . self::convertConfigPrefix($prefix), "string"));
				if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];
			}
		}

		//多言語化の方の値を取得できなかった場合
		$val = trim(soyshop_get_item_attribute_value($itemId, $key, "string"));
		if(!strlen($val)) {	//なければ親商品も調べる
			$parentId = soyshop_get_parent_id_by_child_id($itemId);
			if($parentId > 0) $val = trim(soyshop_get_item_attribute_value($parentId, $key, "string"));
		}

		$v[$prefix][$itemId][$k] = $val;

		return $v[$prefix][$itemId][$k];
	}

	private static function _getFieldValueByItemOrderId(string $key, int $itemOrderId, string $prefix="jp"){

		//管理画面での多言語化 管理画面優先
		if(defined("SOYSHOP_ADMIN_LANGUAGE") && SOYSHOP_ADMIN_LANGUAGE != "jp"){
			$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . self::convertConfigPrefix(SOYSHOP_ADMIN_LANGUAGE))->getValue());
			if(strlen($v)) return $v;

			if(SOYSHOP_ADMIN_LANGUAGE != $prefix){
				$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . self::convertConfigPrefix($prefix))->getValue());
				if(strlen($v)) return $v;
			}
		//公開側
		}else if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . self::convertConfigPrefix(SOYSHOP_PUBLISH_LANGUAGE))->getValue());
			if(strlen($v)) return $v;

			if(SOYSHOP_PUBLISH_LANGUAGE != $prefix){
				$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . self::convertConfigPrefix($prefix))->getValue());
				if(strlen($v)) return $v;
			}
		}

		//多言語化の方の値を取得できなかった場合
		return trim((string)self::_getByItemOrderId($itemOrderId, $key)->getValue());
	}

	private static function _getByItemOrderId(int $itemOrderId, string $key){
		$sql = "SELECT attr.* FROM soyshop_item_attribute attr ".
				"INNER JOIN soyshop_orders os ".
				"ON attr.item_id = os.item_id ".
				"WHERE os.id = :itemOrderId ".
				"AND attr.item_field_id = :fieldId";

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$res = $dao->executeQuery($sql, array(":itemOrderId" => $itemOrderId, ":fieldId" => "item_option_" . $key));
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0]) && isset($res[0]["item_value"]) && strlen($res[0]["item_value"])) return $dao->getObject($res[0]);

		//子商品である場合は親商品の設定を調べる
		$itemId = soyshop_get_hash_table_dao("item_orders")->getItemIdById($itemOrderId);
		if(!is_numeric($itemId)) $itemId = 0;
		return soyshop_get_item_attribute_object(soyshop_get_parent_id_by_child_id($itemId), "item_option_" . $key);
	}

	//多言語のプレフィックスでプラグイン側で決めたプレフィックスに変換する 例：zhをcnに変換
	private static function convertConfigPrefix(string $prefix="jp"){
		static $cnf;
		if(is_null($cnf)){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$cnf = UtilMultiLanguageUtil::getConfig();
		}
		return (isset($cnf[$prefix]["prefix"])) ? $cnf[$prefix]["prefix"] : $prefix;
	}

	//文字列エスケープしつつ、エスケープしてはいけない文字列を元に戻す
	private static function escapeString(string $str){
		$str = trim($str);
		if(!strlen($str)) return "";
		$str = htmlspecialchars($str, ENT_QUOTES, "UTF-8");

		$old = array("#039;");
		$new = array("'");

		for($i = 0; $i < count($old); $i++){
			switch($i){
				case 0;	//&#039;を変換する
					$str = str_replace("&", "", $str);
					$str = str_replace("amp;", "", $str);
					break;
			}
			$str = str_replace($old[$i], $new[$i], $str);
		}

		return $str;
	}
}
