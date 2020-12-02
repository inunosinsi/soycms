<?php
SOY2::import("domain.cms.DataSets");
class CustomSearchFieldUtil{

    const PLUGIN_PREFIX = "csf";    //csf:id="***"

    const TYPE_STRING = "string";
    const TYPE_TEXTAREA = "textarea";
    const TYPE_RICHTEXT = "richtext";
    const TYPE_INTEGER = "integer";
    const TYPE_RANGE = "range";
    const TYPE_CHECKBOX = "checkbox";
    const TYPE_RADIO = "radio";
    const TYPE_SELECT = "select";

    public static function getConfig(){
        return DataSets::get("custom_search.config", array());
    }

    public static function saveConfig($values){
		return DataSets::put("custom_search.config", $values);
    }

    public static function getTypeList(){
        return array(
            self::TYPE_STRING => "文字列",
            self::TYPE_TEXTAREA => "複数行文字列",
            self::TYPE_RICHTEXT => "リッチテキスト",
            self::TYPE_INTEGER => "数字",
            self::TYPE_RANGE => "数字(範囲)",
            self::TYPE_CHECKBOX => "チェックボックス",
            self::TYPE_RADIO => "ラジオボタン",
            self::TYPE_SELECT => "セレクトボックス"
        );
    }

    public static function getTypeText($key){
        $list = self::getTypeList();
        return (isset($list[$key])) ? $list[$key] : "";
    }

    public static function checkIsType($type){
        $list = self::getTypeList();
        return (isset($list[$type]));
    }

	public static function getParameter($key){
		$session = SOY2ActionSession::getUserSession();
		if(isset($_GET[$key])){
			$session->setAttribute("soycms_custom_search:" . $key, $_GET[$key]);
			$params = $_GET[$key];
		}else if(isset($_GET["reset"])){
			$session->setAttribute("soycms_custom_search:" . $key, array());
			if(!defined("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY", true);	//リセットのときも初回表示として扱う
			$params = array();
		}else{
			$params = $session->getAttribute("soycms_custom_search:" . $key);
			if(is_null($params)) {
				if(!defined("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY", true);	//検索フォームを初めて表示したときの定数
				$params = array();
			}
		}
		
		if(!defined("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY", false);	//検索フォームの初回表示でないとき

		return $params;
	}
}
