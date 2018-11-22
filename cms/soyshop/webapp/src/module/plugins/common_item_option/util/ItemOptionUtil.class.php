<?php

class ItemOptionUtil {

	public static function getTypes(){
		return array(
			"select" => "セレクトボックス",
			"radio" => "ラジオボタン",
			"text" => "テキスト"
		);
	}

	public static function getOptions(){
		return self::_getOptions();
	}

	public static function saveOptions($opts){
		SOYShop_DataSets::put("item_option", soy2_serialize($opts));
	}

	private static function _getOptions(){
		static $opts;
		if(is_null($opts)){
			$options = SOYShop_DataSets::get("item_option", null);
			$opts = (isset($options)) ? soy2_unserialize($options) : array();
		}
		return $opts;
	}

	public static function getFieldValue($key, $itemId, $prefix = "jp"){
		return self::_getFieldValue($key, $itemId, $prefix);
	}

	public static function getFieldValueByItemOrderId($key, $itemOrderId, $prefix = "jp"){
		return self::_getFieldValueByItemOrderId($key, $itemOrderId, $prefix);
	}

	/**
	 * 設定に従いフォームを組み立てる
	 */
	public static function buildOptions($key, $conf, $itemId, $prefix = "jp"){
		$v = self::_getFieldValue($key, $itemId, $prefix);
		if(!strlen($v)) return "";

		$name = "item_option[" . $key . "]";
		$type = (isset($conf["type"])) ? $conf["type"] : "select";

		return self::_buildOpt($name, $type, $v);
	}

	public static function buildOptionsWithSelected($key, $conf, SOYShop_ItemOrder $itemOrder, $selected, $prefix = "jp", $isBr = true){
		$v = self::_getFieldValue($key, $itemOrder->getItemId(), $prefix);
		if(!strlen($v)) return "";

		$name = "item_option[" . $itemOrder->getId() . "][" . $key . "]";
		$type = (isset($conf["type"])) ? $conf["type"] : "select";

		return self::_buildOpt($name, $type, $v, $selected, $isBr);
	}

	public static function buildOption($name, $type, $fieldValue, $selected, $isBr = true){
		return self::_buildOpt($name, $type, $fieldValue, $selected, $isBr);
	}

	private static function _buildOpt($name, $type, $fieldValue, $selected = null, $isBr = true){
		$opts = explode("\n", trim($fieldValue));
		$selected = trim(htmlspecialchars($selected, ENT_QUOTES, "UTF-8"));
		if(!strlen($selected)) $selected = null;

		//選択したタイプによって、HTMLの出力を変える
		switch($type){
			case "text":
				return "<input type=\"text\" name=\"" . $name . "\" value=\"" . $selected . "\" >";
			case "radio":
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
			case "select":
			default:
				$html = array();
				$html[] = "<select name=\"" . $name . "\">";

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

	/**
	 * 値を取得するメソッド
	 * @param string key, integer itemId, string prefix
	 * @return object SOYShop_ItemAttribute
	 */
	private static function _getFieldValue($k, $itemId, $prefix = "jp"){
		static $v;
		if(is_null($v)) $v = array();
		if(isset($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];

		$key = "item_option_" . $k;

		if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$v[$prefix][$itemId][$k] = trim(self::_get($itemId, $key . "_" . SOYSHOP_PUBLISH_LANGUAGE)->getValue());
			if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];

			if(SOYSHOP_PUBLISH_LANGUAGE != $prefix){
				$v[$prefix][$itemId][$k] = trim(self::_get($itemId, $key . "_" . $prefix)->getValue());
				if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];
			}
		}

		//多言語化の方の値を取得できなかった場合
		$v[$prefix][$itemId][$k] = trim(self::_get($itemId, $key)->getValue());
		return $v[$prefix][$itemId][$k];
	}

	private static function _get($itemId, $key){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			return $dao->get($itemId, $key);
		}catch(Exception $e){
			return new SOYShop_ItemAttribute();
		}
	}

	private static function _getFieldValueByItemOrderId($key, $itemOrderId, $prefix = "jp"){

		if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . SOYSHOP_PUBLISH_LANGUAGE)->getValue());
			if(strlen($v)) return $v;

			if(SOYSHOP_PUBLISH_LANGUAGE != $prefix){
				$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . $prefix)->getValue());
				if(strlen($v)) return $v;
			}

		//管理画面での多言語化
		}else if(defined("SOYSHOP_ADMIN_LANGUAGE") && SOYSHOP_ADMIN_LANGUAGE != "jp"){
			$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . SOYSHOP_ADMIN_LANGUAGE)->getValue());
			if(strlen($v)) return $v;

			if(SOYSHOP_ADMIN_LANGUAGE != $prefix){
				$v = trim(self::_getByItemOrderId($itemOrderId, $key . "_" . $prefix)->getValue());
				if(strlen($v)) return $v;
			}
		}

		//多言語化の方の値を取得できなかった場合
		return trim(self::_getByItemOrderId($itemOrderId, $key)->getValue());
	}

	private static function _getByItemOrderId($itemOrderId, $key){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		$sql = "SELECT attr.* FROM soyshop_item_attribute attr ".
				"INNER JOIN soyshop_orders os ".
				"ON attr.item_id = os.item_id ".
				"WHERE os.id = :itemOrderId ".
				"AND attr.item_field_id = :fieldId";

		try{
			$res = $dao->executeQuery($sql, array(":itemOrderId" => $itemOrderId, ":fieldId" => "item_option_" . $key));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $dao->getObject($res[0]) : new SOYShop_ItemAttribute();
	}
}
