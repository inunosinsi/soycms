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

	public static function buildOption($name, $type, $fieldValue, $selected, $isBr = true, $editMode = false){
		return self::_buildOpt($name, $type, $fieldValue, $selected, $isBr, $editMode);
	}

	private static function _buildOpt($name, $type, $fieldValue, $selected = null, $isBr = true, $editMode = false){
		$opts = explode("\n", trim($fieldValue));
		$selected = self::escapeString($selected);
		if(!strlen($selected)) $selected = null;
		//if(is_null($selected) && $editMode) $type = "text";	//管理画面で編集の場合は選択がnullの場合はテキストフォームを出力する

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
			$v[$prefix][$itemId][$k] = trim(self::_get($itemId, $key . "_" . self::convertConfigPrefix(SOYSHOP_PUBLISH_LANGUAGE))->getValue());
			if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];

			if(SOYSHOP_PUBLISH_LANGUAGE != $prefix){
				$v[$prefix][$itemId][$k] = trim(self::_get($itemId, $key . "_" . self::convertConfigPrefix($prefix))->getValue());
				if(strlen($v[$prefix][$itemId][$k])) return $v[$prefix][$itemId][$k];
			}
		}

		//多言語化の方の値を取得できなかった場合
		$val = trim(self::_get($itemId, $key)->getValue());
		if(!strlen($val)) {	//なければ親商品も調べる
			$parentId = soyshop_get_parent_id_by_child_id($itemId);
			if($parentId > 0) $val = trim(self::_get($parentId, $key)->getValue());
		}

		$v[$prefix][$itemId][$k] = $val;

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

		if(isset($res[0]) && isset($res[0]["item_value"]) && strlen($res[0]["item_value"])) return $dao->getObject($res[0]);

		//子商品である場合は親商品の設定を調べる
		$itemId = self::itemOrderDao()->getItemIdById($itemOrderId);
		$parentId = soyshop_get_parent_id_by_child_id($itemId);
		if($parentId === 0) return new SOYShop_ItemAttribute();

		try{
			return $dao->get($parentId, "item_option_" . $key);
		}catch(Exception $e){
			return new SOYShop_ItemAttribute();
		}
	}

	//多言語のプレフィックスでプラグイン側で決めたプレフィックスに変換する 例：zhをcnに変換
	private static function convertConfigPrefix($prefix = "jp"){
		static $config;
		if(is_null($config)){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$config = UtilMultiLanguageUtil::getConfig();
		}
		return (isset($config[$prefix]["prefix"])) ? $config[$prefix]["prefix"] : $prefix;
	}

	//文字列エスケープしつつ、エスケープしてはいけない文字列を元に戻す
	private static function escapeString($str){
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

	private static function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
