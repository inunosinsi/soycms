<?php

class CustomSearchFieldUtil{

    const PLUGIN_PREFIX = "csf";        //csf:id="***"
    const PLUGIN_CATEGORY_PREFIX = "c_csf"; //c_csf:id="***"

    const TYPE_STRING = "string";
    const TYPE_TEXTAREA = "textarea";
    const TYPE_RICHTEXT = "richtext";
    const TYPE_INTEGER = "integer";
    const TYPE_RANGE = "range";
    const TYPE_CHECKBOX = "checkbox";
    const TYPE_RADIO = "radio";
    const TYPE_SELECT = "select";
	const TYPE_URL = "url";

	CONST CONVERT_MODE_START = 0;
	const CONVERT_MODE_END = 1;

    public static function getConfig(){
        $config = SOYShop_DataSets::get("custom_search.config", array());
		/**
		 *隠しモード soyshop.site.prepare拡張ポイントで$GLOBALS["csf_config_advanced"]にカラムの情報を入れておくと、ここで追加できる
		 * $GLOBALS["csf_config_advanced"] = array(
		 *	"fieldId" => array("type" => ""),
		 * 	...
		 * );
		 */
		if(isset($GLOBALS["csf_config_advanced"]) && is_array($GLOBALS["csf_config_advanced"]) && count($GLOBALS["csf_config_advanced"])){
			foreach($GLOBALS["csf_config_advanced"] as $fieldId => $field){
				$config[$fieldId] = $field;
			}
		}
		return $config;
    }

    public static function saveConfig($values){
            return SOYShop_DataSets::put("custom_search.config", $values);
    }

    public static function getCategoryConfig(){
            return SOYShop_DataSets::get("custom_search.category", array());
    }

    public static function saveCategoryConfig($values){
            return SOYShop_DataSets::put("custom_search.category", $values);
    }

    public static function getSearchConfig(){
        return SOYShop_DataSets::get("custom_search.search_config", array(
            "search" => array(
            	"single" => 1,
            	"parent" => 1,
            	"child" => 0,
            	"download" => 1,
            	"set_mult_lang" => 0
        	)
        ));
    }

    public static function saveSearchConfig($values){
        foreach(array("single", "parent", "child", "download") as $t){
            $values["search"][$t] = (isset($values["search"][$t])) ? (int)$values["search"][$t] : 0;
        }
        return SOYShop_DataSets::put("custom_search.search_config", $values);
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
            self::TYPE_SELECT => "セレクトボックス",
			self::TYPE_URL => "URL"
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

    public static function getIsOpenCategoryList(){
        try{
            $categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getByIsOpen(1);
        }catch(Exception $e){
            return array();
        }

        if(!count($categories)) return array();

        $list = array();

        foreach($categories as $category){
            $list[$category->getId()] = $category->getName();
        }

        return $list;
    }

    public static function getCustomSearchItemListPages(){
        static $list;
        if(is_null($list)){
            $list = array();
            try{
                $pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_LIST);
            }catch(Exception $e){
                return $list;
            }
            if(!count($pages)) return $list;

            foreach($pages as $page){
                $moduleId = $page->getPageObject()->getModuleId();
                if(isset($moduleId) && strpos($moduleId, "custom_search_field") === 0){
                    $list[$page->getId()] = $page->getName();
                }
            }
        }
        return $list;
    }

	public static function str2timestamp($str, $mode = self::CONVERT_MODE_START){
		if(!strlen($str)) return null;

		//@ToDo フォーマットに合わせてバリエーションを増やす
		if(strpos($str, "/")){	//YYYY/mm/ddの形式
			$v = explode("/", $str);
		}else if(strpos($str, "-")){	//YYYY-mm-ddの形式
			$v = explode("-", $str);
		}else{
			//@ToDo YYYY年mm月dd日の場合
		}

		if(count($v) < 3) return null;

		$v1 = (int)trim($v[0]);
		if(strlen($v1) === 4){
			$year = $v1;
			$month = (int)trim($v[1]);
			$day = (int)trim($v[2]);
		}else{
			// ?
			$year = 0;
			$month = 0;
			$day = 0;
		}

		if($mode == self::CONVERT_MODE_START){
			return mktime(0, 0, 0, $month, $day, $year);
		}else{
			return mktime(0, 0, 0, $month, $day + 1, $year) - 1;
		}
	}

	public static function getParameter($key){
		$session = SOY2ActionSession::getUserSession();
		if(isset($_GET[$key])){
			$session->setAttribute("soyshop_custom_search:" . $key, $_GET[$key]);
			$params = $_GET[$key];
		}else if(isset($_GET["reset"])){
			$session->setAttribute("soyshop_custom_search:" . $key, array());
			if(!defined("CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CUSTOM_SEARCH_FIRST_TIME_DISPLAY", true);	//リセットのときも初回表示として扱う
			$params = array();
		}else{
			$params = $session->getAttribute("soyshop_custom_search:" . $key);
			if(is_null($params)) {
				if(!defined("CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CUSTOM_SEARCH_FIRST_TIME_DISPLAY", true);	//検索フォームを初めて表示したときの定数
				$params = array();
			}
		}

		if(!defined("CUSTOM_SEARCH_FIRST_TIME_DISPLAY")) define("CUSTOM_SEARCH_FIRST_TIME_DISPLAY", false);	//検索フォームの初回表示でないとき

		return $params;
	}
}
